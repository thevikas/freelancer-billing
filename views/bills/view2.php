<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$billing = $project['billing'];

if (0)
{

    /* @var $this yii\web\View */
    /* @var $model app\models\Bill3 */

    $this->title = $model['id_invoice'];
    $this->params['breadcrumbs'][] = ['label' => 'Bills', 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;
    \yii\web\YiiAsset::register($this);
?>
    <div class="bill3-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <?= DetailView::widget([
            'model'      => $model,
            'attributes' => [
                'id_invoice',
                'client',
                'dated',
                'hours',
            ],
        ]) ?>

    </div>
<?php }

$conversion = false;
if (!empty($project['conversion_in_invoice']) && $project['conversion_in_invoice'])
{
    $conversion = true;
}

$project['ccyp'] = $ccy_precision;

function prefix_ccy($project, $amount)
{
    $ccyp = $project['ccyp'];
    $symbols = [
        'INR'  => '&#x20B9;',
        'USD'  => '$',
        'BTC'  => 'BTC',
        'Sats' => '(BTC) Satoshis',
    ];
    if (is_array($project))
    {
        $rate = $symbols[$project['ccy']];
    }
    else if (is_string($project))
    {
        $rate = $symbols[$project];
    }

    return $rate . " " . round($amount, $ccyp);
}
?>


<div class="header-bottom row">

    <div class="col-lg-6 col-md-6 col-12 header-bottom-left">
        <h3>
            <img class="icon-invoice" src="/images/invoice.png" alt="Invoice Icon"> INVOICE TO
        </h3>
        <h2><?= htmlspecialchars($billing['name']) ?></h2>
        <p style="margin-bottom: 10px; line-height: 22px;">
            <?= nl2br(htmlspecialchars($billing['address'])) ?>
        </p>
        <p style="margin-bottom: 10px;">
            <img class="icon-mail" src="/images/mail.png" alt="Email Icon"> <?= htmlspecialchars($billing['email']) ?>
        </p>
        <?php if (!empty($billing['phone']))
        { ?>
            <p>
                <img class="icon-mobile" src="/images/mobile.png" alt="Mobile Icon"> <?= htmlspecialchars($billing['phone']) ?>
            </p>
        <?php } ?>
    </div>


    <div class="col-lg-6 col-md-6 col-12 invoice-header">
        <h1>INVOICE</h1>
        <table class="table table-borderless">
            <thead>
                <tr>
                    <td>
                        <div class="circle">
                            <img class="icon-dollar" src="/images/dollar.png" alt="Dollar Icon">
                        </div>
                    </td>
                    <td>
                        <div class="circle">
                            <img class="icon-calendar" src="/images/calendar.png" alt="Calendar Icon">
                        </div>
                    </td>
                    <td>
                        <div class="circle" style="padding-top: 20px;">
                            <img class="icon-barcode" src="/images/barcode.png" alt="Barcode Icon">
                        </div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Total Due:<br>
                        <strong>
                            <?= !$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr']) ?>
                        </strong>
                    </td>
                    <td>
                        Invoice Date:<br>
                        <strong><?= date('F j, Y', strtotime($invoice['dated'])) ?></strong>
                    </td>
                    <td>
                        Invoice #:<br>
                        <strong><?= $id_invoice ?></strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div><!--BS5 header-bottom-->

<div class="row">
    <div class="col-12">
        <table class="table table-bordered products-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <?php if (!empty($invoice['items'][0]['quantity'])) : ?>
                        <th>Unit Price</th>
                        <th>Hours</th>
                    <?php endif; ?>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoice['items'] as $item)
                { ?>
                    <tr>
                        <td>
                            <h5><?= htmlspecialchars($item['name']) ?></h5>
                            <p><?= !empty($item['des']) ? htmlspecialchars($item['des']) : '' ?></p>
                        </td>
                        <?php if (!empty($item['quantity'])) : ?>
                            <td><?= !empty(trim($item['price'])) ? prefix_ccy($project, $item['price']) : '' ?></td>
                            <td><?= !empty($item['quantity']) ? $item['quantity'] : '' ?></td>
                        <?php endif; ?>
                        <td><?= prefix_ccy($project, $item['amount']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-4 col-12 bottom-left d-none d-md-block">
        <table class="table">
            <thead>
                <tr>
                    <th><strong>Payment Method:</strong> Cheque, Wire, and Bitcoin.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoice['note'])) : ?>
                    <tr>
                        <td><?= htmlspecialchars($invoice['note']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?php /* <p><strong>payments@websitename.com</strong> </p> */ ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php if (1)
                        { ?>
                            <div class="row">
                                <div class="col-4">
                                    <img class="icon-cc" src="/images/cc.png" alt="Credit Card Icon">
                                    <p><strong>Payment</strong></p>
                                </div>
                                <div class="col-8">
                                <p>
                                    <a href="<?= htmlspecialchars($btcpayurl) ?>">
                                        <img src="/images/btcpay.svg" width="209" height="57" alt="Bitcoin Payment">
                                    </a>
                                </p>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (!empty($invoice['unused_pay2addr']))
                        {
                            // Uncomment to display QR code
                            // echo Html::img("/site/qr1?size=150&addr=" . $invoice['unused_pay2addr']);
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php if (!empty($project['showbankdetails']))
                        {
                            $bankname = $project['showbankdetails']; ?>
                            <div style="margin-bottom: -50px">
                                <p><strong>Bank Details</strong></p>
                                <p>Account Name: <?= htmlspecialchars($bankdetails[$bankname]['AccountName']) ?></p>
                                <p>Account Number: <?= htmlspecialchars($bankdetails[$bankname]['AccountNumber']) ?></p>
                                <p>Bank Name: <?= htmlspecialchars($bankdetails[$bankname]['Bank']) ?></p>
                                <p>Branch: <?= htmlspecialchars($bankdetails[$bankname]['Branch']) ?></p>
                                <?php
                                if (!empty($bankdetails[$bankname]['SwitftCode']))
                                    echo "<p>SWIFT Code: " . htmlspecialchars($bankdetails[$bankname]['SwitftCode']) . "</p>";
                                if (!empty($bankdetails[$bankname]['IBAN']))
                                    echo "<p>IBAN: " . htmlspecialchars($bankdetails[$bankname]['IBAN']) . "</p>";
                                if (!empty($bankdetails[$bankname]['IFSC']))
                                    echo "<p>IFSC Code: " . htmlspecialchars($bankdetails[$bankname]['IFSC']) . "</p>";
                                ?>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div> <!-- BS5 -->

    <div class="col-lg-5 col-md-5 col-12 offset-lg-3 totals">
        <table class="table">
            <tbody>
                <tr>
                    <td>SUB TOTAL:</td>
                    <td><?= prefix_ccy($project, $invoice['total']) ?></td>
                </tr>
                <?php /* Uncomment to display tax and discount
            <tr>
                <td>Tax: VAT 20%</td>
                <td>$460.40</td>
            </tr>
            <tr class="discount">
                <td><span>DISCOUNT 5%:</span></td>
                <td><span>-$138.12</span></td>
            </tr>
            */ ?>
                <?php if ($conversion)
                { ?>
                    <tr>
                        <td><span>Currency conversion @ INR <?= $invoice['ccy'][$project['ccy']] ?>:</span></td>
                        <td><span><?= prefix_ccy('INR', $invoice['total_inr']); ?></span></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Due:</td>
                    <td>
                        <?php
                        echo !$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr']);
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <div class="signature">
            <!-- 
        <img class="icon-signature" src="/images/sign.png" alt="Signature">
        <p>Terry Brown</p>
        <p><strong>Accounts Manager</strong></p> 
        -->
        </div>
    </div>

</div><!-- bs5 row -->

<?php if (1)
{ ?>
    <div class="col-lg-5 col-md-5 col-12 bottom-left d-block d-md-none">
        <table class="table">
            <thead>
                <tr>
                    <th><strong>Payment Method:</strong> Cheque, Wire, and Bitcoin.</th>
                </tr>
            </thead>
            <?php if(0) { ?>
            <tbody>
                <tr>
                    <td>
                        <?php /* <p><strong>payments@websitename.com</strong></p> */ ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php /*
                        <img class="icon-cc" src="/images/cc.png" alt="Credit Card Icon">
                        <p><strong>Card Payment</strong></p>
                        <p>We Accept:</p>
                        <p>Visa, Master card, American Express</p>
                        */ ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php /*
                        <p>
                            <strong>Active Interactive</strong><br>
                            256 highland garden,<br>
                            london SW1235,<br>
                            United Kingdom
                        </p>
                        */ ?>
                    </td>
                </tr>
            </tbody>
            <?php } ?>
        </table>
    </div>
<?php } ?>


<?php if(0) { ?>
<div class="row terms">
    <div class="col-12">
        <?php if (!empty($project['terms'])) : ?>
            <p><strong>Terms:</strong> <?= htmlspecialchars($project['terms']) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php } ?>