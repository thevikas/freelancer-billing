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
    "(async function() {new Chart(document.getElementById('chart1'),config_chart1);})();",
    \yii\web\View::POS_READY
);

$this->registerJs(
    "(async function() {new Chart(document.getElementById('chart2'),config_chart2);})();",
    \yii\web\View::POS_READY
);

$this->registerJs(
    "(async function() {new Chart(document.getElementById('chart3'),config_chart3);})();",
    \yii\web\View::POS_READY
);

//round of all $stats values
foreach ($stats as $key => $value)
{
    $stats[$key] = round($value);
}

$ctr=0;

?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('All Projects', ['all'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    makeChart($stats,'chart1',"All Projects");
    makeChart($billing_stats,'chart2','Billed Projects');
    makeChart($est_billing_stats,'chart3','Estimated Bills');
    function makeChart($dstats,$chartid,$title)
    {
        echo "<h2>$title</h2>";
        ?>
        <div style="width: 800px;"><canvas id="<?=$chartid?>"></canvas></div>
        <script type="text/javascript">
        
        const data_<?=$chartid?> = <?php
            $data = [
                'labels' => array_keys($dstats),
                'datasets' => [
                    [
                        'label' => 'Hours',
                        'data' => array_values($dstats),
                        'backgroundColor' => [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 159, 64)',
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ],
                        'hoverOffset' => 4
                    ]
                ]
            ];
            $jsondata = json_encode($data, JSON_PRETTY_PRINT);
            echo $jsondata;
        ?>

        const config_<?=$chartid?> = {
            type: 'pie',
            data: data_<?=$chartid?>,
        };
        </script>
        <?php
    }
    ?>

</div>