<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Bootstrap5 extends AssetBundle
{
    public $sourcePath = '@npm/bootstrap/dist';
    //public $baseUrl = '@web/css/blue-invoice';
    public $css = [
        'css/bootstrap.min.css',
    ];
    public $js = [
        'js/bootstrap.bundle.min.js'
    ];
    public $depends = [
    ];
}
