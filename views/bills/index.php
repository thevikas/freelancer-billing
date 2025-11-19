<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $clients */


//$project_keys = array_keys($clients['projects']);
//only projects which are active
$project_keys = array_keys(array_filter($clients['projects'], function($project) {
    return $project['billingactive'] ?? false;
}));

$this->title = 'Bills';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="bill3-index">

    <h1><?=Html::encode($this->title)?></h1>

    <p>
        <?=Html::a('Create Bill3', ['create'], ['class' => 'btn btn-success'])?>
        -
        <?php
        $def = ['class' => 'btn btn-primary','style' => 'margin-right: 5px;'];
            //filter tags
            foreach ($project_keys as $key)
            {
                $def2 = $def;
                if($key == $filter_client)
                    $def2 = ['class' => 'btn btn-success active','style' => 'margin-right: 5px;'];
                echo Html::a($key, ['bills/index', 'client' => $key], $def2);
            }
        ?>
    </p>

    <?php #Pjax::begin();?>

    <?=GridView::widget([
        'dataProvider' => $dataProvider,
        // highlight rows for paid invoices
        'rowOptions' => function ($model, $key, $index, $grid) {
            if (!empty($model['paid']) || !empty($model['paiddate']) || !empty($model['payment_received']) || !empty($model['receipt_issued'])) {
                return ['class' => 'table-success'];
            }
            return [];
        },
        'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],

        'id_invoice',
        'client',
        'dated',
        [
            'label' => 'Hours',
            'format'  => 'raw',
            'content' => function ($data) use ($ccy_precision)
            {
                if (isset($data['hours']) && $data['hours'] > 0)
                {
                    return round($data['hours'], $ccy_precision);
                }

            },
        ],
        [
            'label' => 'Total',
            'format'  => 'raw',
            'content' => function ($data) use ($ccy_precision,$clients)
            {
                $ccy = $clients['projects'][$data['client']]['ccy'];
                if (!empty($clients['precision'][$ccy]))
                {
                    $ccy_precision = $clients['precision'][$ccy];
                }
                $per_hour = $clients['projects'][$data['client']]['per_hour'];
                if (empty($data['total']))
                {
                    $data['total'] = $data['hours'] * $per_hour;
                }

                if (isset($data['total']) && $data['total'] > 0)
                {
                    //return round($data['total'], $ccy_precision);
                    //echo with ccy
                    return Yii::$app->formatter->asCurrency($data['total'], $ccy);
                }

            },
        ],
        [
            'class' => ActionColumn::class,
            'urlCreator' => function ($action, $model, $key, $index, $column) {
                if($action === 'upload-ts') {
                    return Url::toRoute(['uploads/ts', 'id_invoice' => $model['id_invoice']]);
                }
                else if($action === 'download-pdf') {
                    return Url::toRoute(['bills/download', 'id_invoice' => $model['id_invoice']]);
                }
                else if($action === 'sha256-pdf') {
                    return Url::toRoute(['bills/sha', 'id_invoice' => $model['id_invoice']]);
                }
                else if($action === 'email') {
                    return Url::toRoute(['bills/email', 'id_invoice' => $model['id_invoice']]);
                }
                else if($action === 'mark-paid') {
                    return Url::toRoute(['bills/mark-paid', 'id_invoice' => $model['id_invoice']]);
                }

                return Url::toRoute([$action, 'id' => $model['id_invoice']]);
            },
            'template' => '{upload-ts} {sha256-pdf} {download-pdf} {mark-paid} {view} {email} {update} {delete}', // Add the mark-paid button
            'buttons' => [
                'download-pdf' => function ($url, $model, $key) {
                    return Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-download']),
                        $url, [
                        'title' => Yii::t('app', 'Download Invoice PDF'),
                        'target' => '_blank',
                    ]);
                },
                'email' => function ($url, $model, $key) {
                    if(empty($model['emailsent']))
                        return Html::a(
                                Html::tag('i', '', ['class' => 'fa fa-envelope']),
                            $url, [
                            'title' => Yii::t('app', 'Email Invoice PDF'),
                            'target' => '_blank',
                        ]);
                    else
                        return Html::a(
                            Html::img('/images/mailcheck.svg', ['title' => 'Email Sent', 'style' => 'width: 20px;']),
                            $url, [
                                'title' => Yii::t('app', 'Email Invoice PDF'),
                                'target' => '_blank',
                        ]);
                },
                'sha256-pdf' => function ($url, $model, $key) {
                    return Html::a(
                            Html::tag('i', '', ['class' => 'fas fa-fingerprint']),
                        $url, [
                        'title' => Yii::t('app', 'See Invoice PDF SHA256'),
                        'target' => '_blank',
                    ]);
                },
                'mark-paid' => function ($url, $model, $key) {
                    // consider multiple possible paid flags that may exist in the model
                    $isPaid = !empty($model['paid']) || !empty($model['paiddate']) || !empty($model['payment_received']) || !empty($model['receipt_issued']);
                    if ($isPaid) {
                        return Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-check-circle', 'style' => 'color:green;']),
                            $url, [
                                'title' => Yii::t('app', 'Invoice Paid'),
                                'target' => '_blank',
                        ]);
                    }

                    return Html::a(
                        Html::tag('i', '', ['class' => 'fa fa-receipt']),
                        $url, [
                        'title' => Yii::t('app', 'Mark Paid and Issue Receipt'),
                        'data-confirm' => 'Are you sure you want to mark this invoice as paid and generate a receipt?',
                    ]);
                },
                'upload-ts' => function ($url, $model, $key) {
                    return Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-upload']),
                        $url, [
                        'title' => Yii::t('app', 'Upload Timesheet'),
                        //'data-method' => 'get'
                    ]);
                },
            ],
        ],
    ],
]);?>

    <?php #Pjax::end();?>

</div>
