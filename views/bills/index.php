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

                return Url::toRoute([$action, 'id' => $model['id_invoice']]);
            },
            'template' => '{upload-ts} {sha256-pdf} {download-pdf} {view} {email} {update} {delete}', // Add the {process} button here
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
                        return Html::img('/images/mailcheck.svg', ['title' => 'Email Sent', 'style' => 'width: 20px;']);
                },
                'sha256-pdf' => function ($url, $model, $key) {
                    return Html::a(                            
                            Html::tag('i', '', ['class' => 'fas fa-fingerprint']),
                        $url, [
                        'title' => Yii::t('app', 'See Invoice PDF SHA256'),
                        'target' => '_blank',
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
