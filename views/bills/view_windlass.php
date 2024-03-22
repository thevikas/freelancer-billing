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

        <h1><?=Html::encode($this->title)?></h1>

        <?=DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'id_invoice',
            'client',
            'dated',
            'hours',
        ],
    ])?>

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

    <div class="large-6 medium-6 columns header-bottom-left">

        <h3><img class="icon-invoice" src="/images/invoice.png"></i>INVOICE TO</h3>
        <h2><?=$billing['name']?></h2>
        <p style="margin-bottom:10px;line-height:22px;">
            <?=str_replace("\n", "<br/>", $billing['address'])?>
        </p>

        <p style="margin-bottom:10px;"><img class="icon-mail" src="/images/mail.png"></i><?=$billing['email']?></p>
        <p><img class="icon-mobile" src="/images/mobile.png"></i><?=$billing['phone']?></p>

    </div>

    <div class="large-6 medium-6 columns invoice-header">

        <h1>INVOICE</h1>

        <table>
            <thead>
                <tr>
                    <td>
                        <div class="circle"><img class="icon-dollar" src="/images/dollar.png"></div>
                    </td>
                    <td>
                        <div class="circle"><img class="icon-calendar" src="/images/calendar.png"></div>
                    </td>
                    <td>
                        <div class="circle" style="padding-top:20px;"><img class="icon-barcode" src="/images/barcode.png"></div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Total Due:<br>
                        <strong><?=!$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr'])?></strong>
                    </td>
                    <td>
                        Invoice Date:<br>
                        <strong><?=date('F j, Y', strtotime($invoice['dated']))?></strong>
                    </td>
                    <td>
                        Invoice #:<br>
                        <strong><?=$id_invoice?></strong>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

</div><!--header-bottom-->


<div class="row">
    <div class="large-12 columns">
        <table class="products-table" border=1>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <?php if (!empty($invoice['items'][0]['quantity'])): ?>
                        <th>Unit Price</th>
                        <th>Hours</th>
                    <?php endif;?>
                    <th class="t">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
foreach ($invoice['items'] as $item)
{
    ?>
                    <tr>
                        <td>
                            <h5><?=$item['name']?></h5>
                            <p><?=!empty($item['des']) ? $item['des'] : ''?></p>
                        </td>
                        <?php if (!empty($item['quantity'])): ?>
                            <td><?=!empty(trim($item['price'])) ? prefix_ccy($project, $item['price']) : ''?></td>
                            <td><?=!empty($item['quantity']) ? $item['quantity'] : ''?></td>
                        <?php endif;?>
                        <td class="t"><?=prefix_ccy($project, $item['amount'])?></td>
                    </tr>

                <?php
}
?>
            </tbody>
        </table>
    </div>
</div>


<div class="row">
    <div class="large-4 medium-4 small-12 columns bottom-left show-for-medium-up ">
        <table>
            <thead>
                <tr>
                    <th><strong>Payment Method:</strong> Cheque, Wire and Bitcoin.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoice['note'])): ?>
                    <tr>
                        <td><?=$invoice['note']?></p>
                        </td>
                    </tr>
                <?php endif;?>
                <tr>
                    <td><?php /*<p><strong>payments@websitename.com</strong> */?></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php if (1)
{
    ?>
                            <img class="icon-cc" src="/images/cc.png">
                            <p><strong>Payment</strong></p>
                            <p>Lightning invoice on request</p>
                        <?php }?>

                        <p><br/><br/>
                            <strong>Bank Details</strong><br/>
                            Account Name: Vikas Yadav<br/>
                        Account Number: 054-085949-006<br/>
                        Bank Name: HSBC<br/>
                        Branch: JMD Regent Square, Gurgaon<br/>
                        SWIFT Code: HSBCINBB<br/>
                        IFSC Code: HSBC0110005

                        </p>

                        <?php if (!empty($invoice['unused_pay2addr']))
{
    //massive privacy breach bro tro leak out addresses
    //echo Html::img("/site/qr1?size=150&addr=" . $invoice['unused_pay2addr']);
}?>
                    </td>
                </tr>
                <tr>
                    <td><?php /*<p>
<strong>Active Interactive</strong><br>
256 highland garden,<br>
london SW1235,<br>
United Kingdom
</p>*/?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="large-5 medium-5 small-12 large-offset-3 columns totals">
        <table>
            <tbody>
                <tr>
                    <td>SUB TOTAL:</td>
                    <td><?=prefix_ccy($project, $invoice['total'])?></td>
                </tr>
                <?php /*<tr>
<td>Tax: VAT 20%</td>
<td>$460.40</td>
</tr>
<tr class="discount">
<td><span>DISCOUNT 5%:</span></td>
<td><span>-$138.12</span></td>
</tr>*/?>
                <?php if ($conversion)
{

    ?>
                    <tr>
                        <td><span>Currency conversion @ INR <?=$invoice['ccy'][$project['ccy']]?>:</span></td>
                        <td><span><?=prefix_ccy('INR', $invoice['total_inr']);?></span></td>
                    </tr><?php
}?>

            </tbody>
            <tfoot>
                <tr>
                    <td>Total Due:</td>
                    <td><?php
echo !$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr']);
?></td>
                </tr>
            </tfoot>
        </table>
        <div class="signature">
            <!-- <img class="icon-signature" src="/images/sign.png">
			<p>Terry Brown</p>
			<p><strong>Accounts Manager</strong></p> -->
        </div>
    </div>
</div>

<?php if (1)
{
    ?>
    <!--This section enables for smaller screens and phones-->
    <div class="large-5 medium-5 small-12 columns bottom-left show-for-small-only">
        <table>
            <thead>
                <tr>
                    <th><strong>Payment Method:</strong> Cheque, Wire and Bitcoin.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php /*
    <img class="icon-cc" src="/images/cc.png">
    <p><strong>Card Payment</strong></p>
    <p>We Accept:</p>
    <p>Visa, Master card, American Express</p>
     */?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>
                            <?php /*
    <strong>Active Interactive</strong><br>
    256 highland garden,<br>
    london SW1235,<br>
    United Kingdom
    </p>
    <*/?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
<?php }?>

<div class="row terms">
    <div class="large-12 columns">
        <?php if (!empty($project['terms'])): ?>
            <p><strong>Terms:</strong> <?=$project['terms']?></p>
        <?php endif;?>
    </div>
</div>