<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;

$billing = $project['billing'];


/*
class LoginForm extends Model
{
    public $to_email;
    public $to_name;
    public $from_email;
    public $from_name;

*/

//show form
?>
<div class="invoice-email-form">

    <?php
    $form = ActiveForm::begin();
    ?>

    <?= $form->field($model, 'email_subject')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'to_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'to_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'from_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'from_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_invoice')->textInput() ?>

    <?= $form->field($model, 'invoice_pdf_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'timesheet_csv_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'invoice_month')->textInput(['maxlength' => true]) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Send', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<pre>
<?=$this->render('_timesheet_txt', ['tasks' => $model->tasks])?>
</pre>