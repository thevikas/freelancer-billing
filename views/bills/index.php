<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bills';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill3-index">

    <h1><?=Html::encode($this->title)?></h1>

    <p>
        <?=Html::a('Create Bill3', ['create'], ['class' => 'btn btn-success'])?>
    </p>

    <?php Pjax::begin();?>

    <?=GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],

        'id_invoice',
        'client',
        'dated',
        [
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
            'class' => ActionColumn::class,
            'urlCreator' => function ($action, $model, $key, $index, $column) {
                if($action === 'upload-ts') {
                    return Url::toRoute(['uploads/ts', 'id_invoice' => $model['id_invoice']]);
                }
                else if($action === 'download-pdf') {
                    return Url::toRoute(['bills/download', 'id_invoice' => $model['id_invoice']]);
                }
                

                return Url::toRoute([$action, 'id' => $model['id_invoice']]);
            },
            'template' => '{upload-ts} {download-pdf} {view} {update} {delete}', // Add the {process} button here
            'buttons' => [
                'download-pdf' => function ($url, $model, $key) {
                    return Html::a(                            
                            Html::tag('i', '', ['class' => 'fa fa-download']),
                        $url, [
                        'title' => Yii::t('app', 'Download Invoice PDF'),
                        //'data-method' => 'get'
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

    <?php Pjax::end();?>

</div>
