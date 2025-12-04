<?php

namespace app\controllers;

use app\models\Bill;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * BillsController implements the CRUD actions for Bill model.
 */
class BillsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();
    }

    public function getClients()
    {
        return json_decode(file_get_contents($_ENV['RATES_JSON_FILE']), true);
    }

    /**
     * Lists all Bill models.
     * @return mixed
     */
    public function actionIndex($client = null)
    {
        $clients = $this->clients;
        [$allbills, $allpayments] = Bill::loadfiles($client);

        foreach ($allpayments as $id_invoice => $paymentInfo)
        {
            if (isset($allbills[$id_invoice]))
            {
                $allbills[$id_invoice] = array_merge($allbills[$id_invoice], $paymentInfo);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $allbills,
            'sort'      => [
                'attributes' => ['id_invoice', 'client', 'dated'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider'  => $dataProvider,
            'clients'  => $clients,
            'filter_client'   => $client,
            'ccy_precision' => $clients['precision']['default'],
        ]);
    }

    /**
     * Lists all Bill models.
     * @return mixed
     */
    public function actionEmail($id_invoice)
    {
        $bills = Bill::loadfiles();
        $clients = $this->clients;
        $invoice = $bills[$id_invoice];
        $project = $clients['projects'][$bills[$id_invoice]['client']];

        //if form post
        if (Yii::$app->request->post())
        {
            $model = new \app\models\InvoiceEmailForm();
            $model->load(Yii::$app->request->post());
            if ($model->validate())
            {
                $model->send();
                //add notification
                Yii::$app->session->setFlash('success', 'Email sent');
                //update json with emailsent true
                //TODO $bills[$id_invoice]['emailsent'] = true;
                //TODO $json_file = $_ENV['BILLS_JSON_DIR'] . '/' . $id_invoice . '-' . $invoice['client'] . '.json';
                //TODO file_put_contents($json_file, json_encode($bills[$id_invoice], JSON_PRETTY_PRINT));
                return $this->redirect(['index']);
            }
        }

        $project['email']['to'] = $project['email']['to'] ?? $project['billing']['email'];
        $project['email']['name'] = $project['email']['name'] ?? $project['billing']['name'];
        $project['email']['subject'] = $project['email']['subject'] ?? "{{client_name}} Invoice #{{inum}} for the month of {{month}}";

        $model = new \app\models\InvoiceEmailForm();
        $model->id_invoice = $id_invoice;
        $pdf_path = $_ENV['BILLS_PDF_DIR'];
        $model->invoice_pdf_path =  $pdf_path . '/Invoice-' . $id_invoice . '-' . $invoice['client'] . '.pdf';
        $model->to_email = $project['email']['to'];
        $model->to_name = $project['email']['name'];
        $model->from_email = $_ENV['SENDER_EMAIL'];
        $model->from_name = $_ENV['SENDER_NAME'];
        $model->timesheet_csv_path = $_ENV['BILLS_JSON_DIR'] . '/' . $id_invoice . '-' . $invoice['client'] . '-ts.csv';

        //month before invoice date
        $model->invoice_month = date('F Y', strtotime($invoice['dated'] . ' -1 month'));

        $model->email_subject = str_replace('{{month}}', $model->invoice_month, $project['email']['subject']);
        $model->email_subject = str_replace('{{client_name}}', $project['billing']['name'], $model->email_subject);
        //inum
        $model->email_subject = str_replace('{{inum}}', $id_invoice, $model->email_subject);

        return $this->render('email', [
            'model' => $model,
            'id_invoice' => $id_invoice,
            'clients'    => $clients,
            'project'    => $project,
            'invoice'    => $invoice,
        ]);
    }

    /**
     * Displays a single Bill model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        [$bills, $payments] = Bill::loadfiles();
        $clients = $this->clients;
        $invoice = $bills[$id];
        $project = $clients['projects'][$bills[$id]['client']];
        if (empty($project['layout']))
        {
            //$this->layout = 'blue-invoice';
            $this->layout = 'bs5-invoice';
        }
        else
        {
            $this->layout = $project['layout'];
        }

        if (empty($invoice['total']))
        {
            $invoice['total'] = $invoice['hours'] * $project['per_hour'];
        }

        $ccy_precision = $clients['precision']['default'];
        if (!empty($clients['precision'][$project['ccy']]))
        {
            $ccy_precision = $clients['precision'][$project['ccy']];
        }

        $invoice['hours'] = round($invoice['hours'], $clients['precision']['default']);
        $invoice['total'] = round($invoice['total'], $ccy_precision);

        $invoice['items'][0] = [
            'name'     => $invoice['item_des'] ?? 'Software development',
            'price'    => $project['per_hour'],
            'quantity' => $invoice['hours'] ?: "",
            'amount'   => $invoice['total'],
        ];

        if (!empty($invoice['extra_items']))
        {
            foreach ($invoice['extra_items'] as &$item)
            {

                $item['quantity'] = $item['price'] = " ";
                if (!empty($item['overtime']))
                {
                    $item['amount'] = ($item['overtime'] * $project['overtime_per_hour']);
                    $item['price'] = $project['overtime_per_hour'];
                    $item['quantity'] = $item['overtime'];
                }

                if (!empty($item['amount']))
                {
                    $invoice['total'] += $item['amount'];
                }
                else
                {
                    throw new \Exception("Do not know how to include extra item in calc");
                }

                $invoice['items'][] = $item;
            }
        }
        $invoice['total_inr'] = round($invoice['total'] * $clients['ccy'][$project['ccy']]);

        $btcpayurl = $clients['bankdetails']['btcpay'];

        if (!empty($payments[$id]))
        {
            $invoice = array_merge($invoice, $payments[$id]);
        }

        $params = [
            'orderId'      => $id,
            'checkoutDesc' => "Invoice " . $id,
            'price'        => $invoice['total'] * 0.85, //discount on btc payment
            'currency'     => $project['ccy'],
        ];

        if ($project['ccy'] == 'BTC')
        {
            $params['price'] = $invoice['total'];
        }

        $btcpayurl .= "&" . http_build_query($params);


        return $this->render('view2', [
            'ccy_precision' => $ccy_precision,
            'id_invoice'    => $id,
            'btcpayurl'     =>  $btcpayurl,
            'invoice'       => $invoice,
            'project'       => $project,
            'bankdetails'   => $clients['bankdetails'],
        ]);
    }

    /**
     * Creates a new Bill model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Bill();

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['view', 'id' => $model->id_bill]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Bill model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['view', 'id' => $model->id_bill]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Bill model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Bill model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Bill the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bill::findOne($id)) !== null)
        {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Echo sha 256 of PDF file and echo
     *
     * @param [type] $id_invoice
     * @return void
     */
    public function actionSha($id_invoice)
    {
        $bills = Bill::loadfiles();

        $BILLS_PDF_DIR = $_ENV['BILLS_PDF_DIR'];

        $clients = $this->clients;

        $invoice = $bills[$id_invoice];

        $pdf_filename = "Invoice-" . $id_invoice . "-" . $invoice['client'] . ".pdf";

        $pdf_path = $BILLS_PDF_DIR . "/" . $pdf_filename;

        if (file_exists($pdf_path))
        {
            echo hash_file('sha256', $pdf_path);
        }
        else
        {
            throw new NotFoundHttpException('The requested PDF does not exist.');
        }

        return;
    }

    /**
     * Download PDF
     */
    public function actionDownload($id_invoice)
    {


        $bills = Bill::loadfiles();

        $BILLS_PDF_DIR = $_ENV['BILLS_PDF_DIR'];

        $clients = $this->clients;

        $invoice = $bills[$id_invoice];

        $pdf_filename = "Invoice-" . $id_invoice . "-" . $invoice['client'] . ".pdf";

        $pdf_path = $BILLS_PDF_DIR . "/" . $pdf_filename;

        if (file_exists($pdf_path))
        {
            return Yii::$app->response->sendFile($pdf_path, $pdf_filename, ['inline' => true]);
        }
        else
        {
            throw new NotFoundHttpException('The requested PDF does not exist.');
        }

        return;
    }

    /**
     * Mark invoice as paid and generate receipt
     */
    public function actionMarkPaid($id_invoice)
    {
        [$bills, $payments] = Bill::loadfiles();

        if (!isset($bills[$id_invoice]))
        {
            throw new NotFoundHttpException('Invoice not found.');
        }

        $invoice = $bills[$id_invoice];
        $clients = $this->clients;
        $project = $clients['projects'][$invoice['client']];

        // Check if already paid
        if (!empty($invoice['paid']) || !empty($invoice['paiddate']))
        {
            Yii::$app->session->setFlash('warning', 'Invoice is already marked as paid.');
            return $this->redirect(['index']);
        }

        // Mark as paid
        $invoice['paid'] = true;
        $invoice['paiddate'] = date('Y-m-d');
        $invoice['payment_received'] = date('Y-m-d H:i:s');
        $invoice['receipt_issued'] = date('Y-m-d H:i:s');

        // Save updated invoice
        $jsonfile = $invoice['jsonfile'];
        unset($invoice['jsonfile']);
        file_put_contents($jsonfile, json_encode($invoice, JSON_PRETTY_PRINT));

        // Generate receipt PDF using Node.js screenshot script
        $receipt_script = Yii::getAlias('@app/screenshot-receipt.js');

        if (file_exists($receipt_script))
        {
            // Execute Node.js script to generate receipt PDF
            $command = sprintf(
                'node %s %s %s 2>&1',
                escapeshellarg($receipt_script),
                escapeshellarg($id_invoice),
                escapeshellarg($invoice['client'])
            );

            exec($command, $output, $return_var);

            if ($return_var !== 0)
            {
                Yii::$app->session->setFlash('warning', 'Invoice marked as paid, but receipt PDF generation failed: ' . implode("\n", $output));
            }
            else
            {
                Yii::$app->session->setFlash('success', 'Invoice marked as paid and receipt PDF generated successfully.');
            }
        }
        else
        {
            // Create a simple receipt record file as fallback
            $receipt_data = [
                'id_invoice' => $id_invoice,
                'client' => $invoice['client'],
                'amount' => $invoice['total'] ?? ($invoice['hours'] * $project['per_hour']),
                'payment_date' => date('Y-m-d'),
                'payment_received' => date('Y-m-d H:i:s'),
                'receipt_issued' => date('Y-m-d H:i:s'),
            ];

            // Save receipt record
            $receipt_file = Yii::getAlias('@app/data') . '/' . $id_invoice . '-' . $invoice['client'] . '-receipt.json';
            file_put_contents($receipt_file, json_encode($receipt_data, JSON_PRETTY_PRINT));

            Yii::$app->session->setFlash('success', 'Invoice marked as paid. Receipt record created. (PDF script not found - run: node screenshot-receipt.js ' . $id_invoice . ' ' . $invoice['client'] . ' to generate PDF)');
        }

        return $this->redirect(['index']);
    }

    /**
     * Display receipt view (similar to invoice view but marked as receipt)
     */
    public function actionReceipt($id)
    {
        [$bills, $payments] = Bill::loadfiles();
        $clients = $this->clients;
        $invoice = $bills[$id];
        $project = $clients['projects'][$bills[$id]['client']];

        if (empty($project['layout']))
        {
            $this->layout = 'bs5-invoice';
        }
        else
        {
            $this->layout = $project['layout'];
        }

        if (empty($invoice['total']))
        {
            $invoice['total'] = $invoice['hours'] * $project['per_hour'];
        }

        $ccy_precision = $clients['precision']['default'];
        if (!empty($clients['precision'][$project['ccy']]))
        {
            $ccy_precision = $clients['precision'][$project['ccy']];
        }

        $invoice['hours'] = round($invoice['hours'], $clients['precision']['default']);
        $invoice['total'] = round($invoice['total'], $ccy_precision);

        $invoice['items'][0] = [
            'name'     => $invoice['item_des'] ?? 'Software development',
            'price'    => $project['per_hour'],
            'quantity' => $invoice['hours'] ?: "",
            'amount'   => $invoice['total'],
        ];

        if (!empty($invoice['extra_items']))
        {
            foreach ($invoice['extra_items'] as &$item)
            {
                $item['quantity'] = $item['price'] = " ";
                if (!empty($item['overtime']))
                {
                    $item['amount'] = ($item['overtime'] * $project['overtime_per_hour']);
                    $item['price'] = $project['overtime_per_hour'];
                    $item['quantity'] = $item['overtime'];
                }

                if (!empty($item['amount']))
                {
                    $invoice['total'] += $item['amount'];
                }
                else
                {
                    throw new \Exception("Do not know how to include extra item in calc");
                }

                $invoice['items'][] = $item;
            }
        }
        $invoice['total_inr'] = round($invoice['total'] * $clients['ccy'][$project['ccy']]);

        return $this->render('receipt', [
            'ccy_precision' => $ccy_precision,
            'id_invoice'    => $id,
            'invoice'       => $invoice,
            'project'       => $project,
            'bankdetails'   => $clients['bankdetails'],
        ]);
    }
}
