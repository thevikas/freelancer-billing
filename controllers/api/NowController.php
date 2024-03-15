<?php

namespace app\controllers\api;

use app\models\Project as ProjectModal;
use Yii;

require_once __DIR__ . '/../../Project.php';
require_once __DIR__ . '/../../functions.php';

/**
 * Post timesheet
 */
class NowController extends \yii\web\Controller
{
    /**
     * Post log in timesheet, data is raw gtimelog format
     *
     * @param [type] $log
     * @return void
     */
    public function actionIndex($log = "")
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();
        $proj = new \gtimelogphp\Project("");
        $tempLogfile = Yii::getAlias('@app/tests/_data/now.log');
        file_put_contents($tempLogfile,date('Y-m-d H:i')  . ": init: dummy\n");
        //public function logNow($fullarg,$logfile,$argv,$gitrepo,$pcname)
        $proj->logNow($log,$tempLogfile,[
            "",$log
        ],false,"Tester1");

        //set json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function actionLast()
    {
        
    }

}
