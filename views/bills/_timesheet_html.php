<?php
/** @var array $tasks */

use yii\helpers\Html;

$this->title = 'Task Report';
$this->params['breadcrumbs'][] = $this->title;

$headers = ['Module','Task','Time (HH:MM)'];
?>

<style type="text/css">
    .task-report table {
        width: 100%;
    }

    .task-report th {
        background-color: #f5f5f5;
    }

    .task-report td {
        text-align: right;
    }

    .task-report td.col0 {
        text-align: left;
    }

    .task-report td.col1 {
        text-align: left;
    }

    .task-report td.col2 {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-weight: bold;
    }    

    .task-report th.col2 {
        text-align: right;
    }    

</style>

<div class="task-report">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <?php foreach ($headers as $i => $header): ?>
                    <th class="col<?=$i?>"><?= Html::encode($header) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($tasks, 1) as $task): 
                $rowcls = '';
                if($task[0] == 'Total') {
                    $rowcls = 'table-info';
                }
                ?>
                <tr class="<?=$rowcls?>">
                    <?php foreach ($task as $i => $column): ?>
                        <td class="col<?= $i ?>">
                            <?= Html::encode($column) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
