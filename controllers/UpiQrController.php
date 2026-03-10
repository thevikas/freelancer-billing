<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Label\Label;

/**
 * UpiQrController generates UPI QR codes following NPCI UPI Deep Link specs.
 *
 * Spec Reference: NPCI UPI Linking Specification v1.0+
 * Format: upi://pay?pa=<VPA>&pn=<NAME>&am=<AMOUNT>&cu=<CURRENCY>&tn=<NOTE>&mc=<MCC>&tr=<REF>
 */
class UpiQrController extends Controller
{
    /**
     * Disable CSRF validation for GET query string requests
     */
    public $enableCsrfValidation = false;

    /**
     * Action: /upi-qr/generate
     *
     * Query Parameters:
     * @param string  pa   (required) Payee VPA e.g. merchant@upi
     * @param string  pn   (required) Payee Name e.g. John Doe
     * @param float   am   (optional) Amount e.g. 100.00
     * @param string  cu   (optional) Currency code, default INR
     * @param string  tn   (optional) Transaction Note / description
     * @param string  mc   (optional) Merchant Category Code (4 digits)
     * @param string  tr   (optional) Transaction Reference / Order ID
     * @param string  tid  (optional) Device / Terminal ID
     * @param string  url  (optional) Merchant URL
     * @param string  mid  (optional) Merchant ID
     * @param int     size   (optional) QR image size in pixels, default 300
     * @param int     margin (optional) QR image margin, default 10
     * @param bool    render (optional) If true, outputs PNG directly instead of JSON, default false
     *
     * Returns: JSON { "qr_url": "https://..." } or PNG image if render=true
     */
    public function actionGenerate()
    {
        $request = Yii::$app->request;

        // ── 1. Required Parameters ─────────────────────────────────────────────
        $pa = trim($request->get('pa', ''));
        $pn = trim($request->get('pn', ''));

        if (empty($pa))
        {
            throw new BadRequestHttpException('Missing required parameter: pa (Payee VPA)');
        }
        if (empty($pn))
        {
            throw new BadRequestHttpException('Missing required parameter: pn (Payee Name)');
        }

        // Validate VPA format  (something@something)
        if (!preg_match('/^[a-zA-Z0-9.\-_]+@[a-zA-Z0-9\.]+$/', $pa))
        {
            throw new BadRequestHttpException('Invalid VPA format for parameter: pa');
        }

        // ── 2. Optional Parameters ─────────────────────────────────────────────
        $am  = $request->get('am',  null);   // Amount
        $cu  = $request->get('cu',  'INR');  // Currency  (must be INR per spec)
        $tn  = $request->get('tn',  null);   // Transaction Note
        $mc  = $request->get('mc',  null);   // Merchant Category Code
        $tr  = $request->get('tr',  null);   // Transaction Reference
        $tid = $request->get('tid', null);   // Terminal ID
        $url = $request->get('url', null);   // Merchant URL
        $mid = $request->get('mid', null);   // Merchant ID

        // Image options
        $size   = (int) $request->get('size',   300);
        $margin = (int) $request->get('margin',  10);
        $render = $request->get('render', false); // If true, output PNG directly

        // Clamp size between 100 and 1000 px for safety
        $size   = max(100, min(1000, $size));
        $margin = max(0,   min(100,  $margin));

        // ── 3. Validate Amount ─────────────────────────────────────────────────
        if ($am !== null)
        {
            if (!is_numeric($am) || (float)$am < 0)
            {
                throw new BadRequestHttpException('Invalid amount: am must be a positive number');
            }
            // Format to 2 decimal places as per UPI spec
            $am = number_format((float)$am, 2, '.', '');
        }

        // ── 4. Build UPI Deep Link String ──────────────────────────────────────
        // Spec: upi://pay?pa=<VPA>&pn=<NAME>[&am=<AMOUNT>][&cu=<CURRENCY>]...
        $upiParams = [
            'pa' => $pa,
            'pn' => $pn,
            'cu' => strtoupper($cu),
        ];

        // Optional fields appended only when provided (keeps QR data minimal)
        if ($am  !== null)  $upiParams['am']  = $am;
        if ($tn  !== null)  $upiParams['tn']  = $tn;
        if ($mc  !== null)  $upiParams['mc']  = $mc;
        if ($tr  !== null)  $upiParams['tr']  = $tr;
        if ($tid !== null)  $upiParams['tid'] = $tid;
        if ($url !== null)  $upiParams['url'] = $url;
        if ($mid !== null)  $upiParams['mid'] = $mid;

        // Build query string — UPI spec requires raw encoding (no + for spaces)
        $queryString = http_build_query($upiParams, '', '&', PHP_QUERY_RFC3986);
        $upiString   = 'upi://pay?' . $queryString;

        // ── 5. Generate QR Code ────────────────────────────────────────────────
        $writer = new PngWriter();

        $qrCode = QrCode::create($upiString)
            ->setEncoding(new Encoding('UTF-8'))
            // UPI spec recommends M (15%) or higher error correction
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->setSize($size)
            ->setMargin($margin)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))       // Black modules
            ->setBackgroundColor(new Color(255, 255, 255)); // White background

        $result = $writer->write($qrCode);

        // ── 6. Save to Webroot and Return URL ─────────────────────────────────
        $uploadDir = Yii::getAlias('@webroot/uploads/upi-qr');
        if (!is_dir($uploadDir))
        {
            mkdir($uploadDir, 0755, true);
        }

        // Build a deterministic filename so repeated identical requests reuse the file
        $filename  = 'upi_' . md5($upiString . $size . $margin) . '.png';
        $filePath  = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        $result->saveToFile($filePath);

        $fileUrl = Yii::$app->request->hostInfo
            . Yii::getAlias('@web')
            . '/uploads/upi-qr/'
            . $filename;

        // ── 7. Return PNG or JSON ──────────────────────────────────────────────
        if ($render)
        {
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $response->headers->set('Content-Type', 'image/png');
            $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
            return file_get_contents($filePath);
        }

        // Return JSON (default behavior)
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'success'    => true,
            'qr_url'     => $fileUrl,
            'upi_string' => $upiString,  // Useful for debugging / deep-link buttons
            'params'     => [
                'pa'   => $pa,
                'pn'   => $pn,
                'am'   => $am,
                'cu'   => strtoupper($cu),
                'tn'   => $tn,
                'mc'   => $mc,
                'tr'   => $tr,
                'size' => $size,
            ],
        ];
    }
}
