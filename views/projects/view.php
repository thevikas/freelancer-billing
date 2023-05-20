<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Project */

/*
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
*/
\yii\web\YiiAsset::register($this);
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

<?=GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],
        'task',
        'spent',
    ],
]);?>
    
</div>
