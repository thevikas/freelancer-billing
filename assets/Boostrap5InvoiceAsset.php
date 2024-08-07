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
class Boostrap5InvoiceAsset extends AssetBundle
{
    public $basePath = '@app/views/Blue';
    public $baseUrl = '@web/css/blue-invoice';
    public $css = [
        //'normalize.css',
        //'foundation.min.css',
        'style.css',
    ];
    public $js = [
    ];
    public $depends = [
        'app\assets\Bootstrap5',
    ];
}
