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
class ChartJs extends AssetBundle
{
    //public $sourcePath = '@npm/bootstrap/dist';
    //public $baseUrl = '@web/css/blue-invoice';
    public $css = [
    ];
    public $js = [
        //'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.3.3/chart.min.js'
        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.3.3/chart.umd.js'
    ];
    public $depends = [
    ];
}
