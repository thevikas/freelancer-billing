<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('All Projects', ['all'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Charts', ['charts'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'format'  => 'raw',
                'label' => 'Name',
                'content' => function ($data)
                {
                    return Html::a($data['name'],['view','projcode' => $data['name']]);
                },
            ],
            [
                'format'  => 'raw',
                'label' => 'Hours',
                'content' => function ($data)
                {
                    return round($data['Total']);
                },
            ],
            [
                'format'  => 'raw',
                'label' => 'Income',
                'content' => function ($data)
                {
                    return round($data['Income']);
                },
            ],
            'Dated',
        ],
    ]); ?>

</div>
