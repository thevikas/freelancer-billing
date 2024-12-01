<?php
/** @var string $to_name */
/** @var string $from_name */
/** @var string $id_invoice */
/** @var array $tasks */
use yii\helpers\Html;

?>
<!DOCTYPE html>
<html>
<head>
    <title><?= Html::encode($subject) ?></title>
</head>
<body>
    <h1>Invoice #<?= Html::encode($id_invoice) ?></h1>
    <p>Dear <?= Html::encode($to_name) ?>,</p>
    <p>Below is the worksheet of <?=$invoice_month?> and the Invoice attached.</p>
    <pre><?=$this->render('_timesheet_txt', ['tasks' => $tasks])?></pre>

    <p>Best regards,</p>
    <p><?= Html::encode($from_name) ?></p>
</body>
</html>
