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

    <?php
    echo $this->render("_proj_header", ['proj' => $proj, 'projcode' => $projcode]);
    ?>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#month" role="tab" aria-controls="home" aria-selected="true">Month</a>
        </li>
        <?php
        foreach ($weeks as $n => $weekdate)
        {
        ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="week<?= $n ?>-tab" data-bs-toggle="tab" data-bs-target="#week<?= $n ?>" href="#week<?= $n ?>" role="tab" aria-controls="week<?= $n ?>" aria-selected="false"><?= $weekdate ?></a>
            </li>
        <?php
        }
        ?>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="month" role="tabpanel" aria-labelledby="month-tab" tabindex="0">
            <?= GridView::widget([
                'dataProvider' => $dataProviderAll,
                'columns'      => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'task',
                    'spent',
                    'times',
                ],
            ]); ?>
        </div>
        <?php
        foreach ($weeks as $n => $weekdate)
        {
        ?>
            <div class="tab-pane fade" id="week<?= $n ?>" role="tabpanel" aria-labelledby="week<?= $n ?>-tab">
                <?= GridView::widget([
                    'dataProvider' => $dataProviderWeeks[$n],
                    'columns'      => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'task',
                        'spent',
                        'times',
                    ],
                ]); ?>
            </div>
        <?php
        }
        ?>
    </div>

</div>