<?php

use yii\helpers\Html;
use yii\grid\GridView;

//require asset ChartJs
use app\assets\ChartJs;
ChartJs::register($this);

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;

//register JS
$this->registerJs(
    "(async function() {new Chart(document.getElementById('acquisitions'),config);})();",
    \yii\web\View::POS_READY
);




?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('All Projects', ['all'], ['class' => 'btn btn-success']) ?>
    </p>

    <div style="width: 800px;"><canvas id="acquisitions"></canvas></div>

    <script type="text/javascript">
        

        const data = {
            labels: [
                'Red',
                'Blue',
                'Yellow'
            ],
            datasets: [{
                label: 'My First Dataset',
                data: [300, 50, 100],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ],
                hoverOffset: 4
            }]
        };

        const config = {
            type: 'pie',
            data: data,
        };
        </script>

</div>