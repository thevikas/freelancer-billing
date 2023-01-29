<?php

use yii\grid\GridView;
use yii\helpers\Html;
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

        ['class' => 'yii\grid\ActionColumn'],
    ],
]);?>

    <?php Pjax::end();?>

</div>
