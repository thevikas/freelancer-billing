<?php

use app\assets\UploaderAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\DataSource $model */
/** @var yii\widgets\ActiveForm $form */
UploaderAsset::register($this);
?>

<div class="data-source-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">

    <div class="col-md-6">
    <?php
    //1 radio button to upload file
    echo Html::radio('uploadtype', true, [
        'value' => 'file', 
        'label' => 'Upload File',
        'onchange' => 'hideAll();$("#up-file").show();'
    ]);
    echo '<br>';

    //file upload    
    echo Html::tag('div', $form->field($model, 'file')->fileInput([
        'accept' => '.csv, .xls, .xlsx',        
    ])->label(false), [
        'id' => 'up-file',
        //'style' => 'display:none;'
    ]);

    //2 radio button to upload file from URL
    echo Html::radio('uploadtype', false, [
        'value' => 'url', 
        'label' => 'Upload File from URL',
        'onchange' => 'hideAll();$("#up-url").show();'
    ]);
    echo '<br>';

    //URL input
    /*
    echo Html::tag('div', $form->field($model, 'url')->textInput(        
        [
            'maxlength' => true,            
        ])->label(false), [
        'id' => 'up-url',
        'style' => 'display:none;'
    ]);
    */

    //2 radio button to upload file from URL
    echo Html::radio('uploadtype', false, [
        'value' => 'text', 
        'label' => 'Upload Text',
        'onchange' => 'hideAll();$("#up-txt").show();'
    ]);

    //URL input
    echo Html::tag('div', $form->field($model, 'text')->textarea()->label(false), [
        'id' => 'up-txt',
        'style' => 'display:none;'
    ]);
    ?>

</div>

    <div class="col-md-6">
    <?php
    function mime2id($mime)
    {
        return str_replace('/','_',$mime);
    }
    // Filetype radio button list displayed vertically
    echo $form->field($model, 'filetype')->radioList(
        $model->getFiletypeOptions(), 
        [
            'item' => function ($index, $label, $name, $checked, $value) {
                return '<div class="form-check">' .
                    Html::radio($name, $checked, [
                        'value' => $value,
                        'class' => 'form-check-input',
                        'id' => 'filetype-' . mime2id($value)
                    ]) .
                    Html::label($label, 'filetype-' . $value, [
                        'class' => 'form-check-label',
                        'id' => 'lab-filetype-' . mime2id($value)
                    ]) .
                    '</div>';
            },
        ]
    );
    ?>
    </div>

    </div>

    <div class="form-group">
        <?= Html::submitButton('Import', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>