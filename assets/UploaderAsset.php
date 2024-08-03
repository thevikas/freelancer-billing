<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class UploaderAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
    ];
    public $js = [
        'js/uploader.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\Bootstrap5',
    ];
}