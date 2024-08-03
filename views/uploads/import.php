<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\DataSource $model */

$this->title = 'Import File';
//$this->params['breadcrumbs'][] = ['label' => 'Data Sources', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->datasource->name, 'url' => ['view', 'id_datasource' => $model->id_datasource]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="data-source-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_fileuploadform', [
        'model' => $model,
    ]) ?>

</div>
