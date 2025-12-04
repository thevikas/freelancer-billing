<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$billing = $project['billing'];

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

<div class="paid-stamp-container" style="position: relative;">
    <div class="paid-stamp" style="position: absolute; top: 100px; right: 300px; transform: rotate(-30deg); z-index: 1000; opacity: 0.5;">
        <svg width="200" height="200" viewBox="0 0 200 200">
            <g>
                <!-- Outer circle -->
                <circle cx="100" cy="100" r="90" fill="none" stroke="#0a0" stroke-width="5"/>
                <!-- Inner circle -->
                <circle cx="100" cy="100" r="85" fill="none" stroke="#0a0" stroke-width="2"/>
                <!-- PAID text -->
                <text x="100" y="75" font-family="Arial" font-size="40" fill="#0a0" text-anchor="middle" font-weight="bold">PAID</text>
                <!-- Date text -->
                <text x="100" y="105" font-family="Arial" font-size="24" fill="#0a0" text-anchor="middle" font-weight="bold">
                    <?= date('d M Y', strtotime($invoice['paiddate'] ?? $invoice['payment_received'] ?? 'now')) ?>
                </text>
                <!-- Amount text -->
                <text x="100" y="135" font-family="Arial" font-size="24" fill="#0a0" text-anchor="middle" font-weight="bold">
                    <?= !$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr']) ?>
                </text>
            </g>
        </svg>
    </div>
</div>

<div class="header-bottom row">

    <div class="col-lg-6 col-md-6 col-12 header-bottom-left">
        <h3>
            <img class="icon-invoice" src="/images/invoice.png" alt="Invoice Icon"> PAYMENT RECEIVED FROM
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
        <h1>RECEIPT</h1>
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
                        Amount Paid:<br>
                        <strong>
                            <?= !$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr']) ?>
                        </strong>
                    </td>
                    <td>
                        Payment Date:<br>
                        <strong><?= date('F j, Y', strtotime($invoice['paiddate'] ?? $invoice['payment_received'] ?? 'now')) ?></strong>
                    </td>
                    <td>
                        Receipt #:<br>
                        <strong>R-<?= $id_invoice ?></strong>
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
                    <th><strong>Payment Received</strong></th>
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
                        <p><strong>This is an official receipt for Invoice #<?= $id_invoice ?></strong></p>
                        <p>Payment received on <?= date('F j, Y', strtotime($invoice['paiddate'] ?? $invoice['payment_received'] ?? 'now')) ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-lg-5 col-md-5 col-12 offset-lg-3 totals">
        <table class="table">
            <tbody>
                <tr>
                    <td>SUB TOTAL:</td>
                    <td><?= prefix_ccy($project, $invoice['total']) ?></td>
                </tr>
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
                    <td>Amount Paid:</td>
                    <td>
                        <?php
                        echo !$conversion ? prefix_ccy($project, $invoice['total']) : prefix_ccy('INR', $invoice['total_inr']);
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <div class="signature">
            <p style="margin-top: 50px; border-top: 1px solid #000; display: inline-block; padding: 5px 20px 0 20px;">
                <strong>Authorized Signature</strong>
            </p>
        </div>
    </div>

</div><!-- bs5 row -->

<style type="text/css">
    body {
        margin: 20px;
    }
</style>
