<?php

namespace app\controllers\api;

use app\models\Project as ProjectModal;
use Yii;

require_once __DIR__ . '/../../Project.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../Report.php';

/**
 * Post timesheet
 */
class NowController extends \yii\web\Controller
{
    public function init()
    {
        parent::init();
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'),Yii::$app->params['envFile']);
        $dotenv->load();       
        
        //ignore csrf
        Yii::$app->request->enableCsrfValidation = false;
    }

    /**
     * Post log in timesheet, data is raw gtimelog format
     *
     * @param [type] $log
     * @return void
     */
    public function actionIndex($log = "")
    {
        $proj = new \gtimelogphp\Project("");
        $tempLogfile = Yii::getAlias('@app/tests/_data/now.log');
        //public function logNow($fullarg,$logfile,$argv,$gitrepo,$pcname)
        $proj->logNow($log,$tempLogfile,[
            "",$log
        ],false,"Tester1");

        //redirect to last
        return $this->actionLast();
    }

    public function actionLast()
    {
        //return json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $logfile = Yii::getAlias($_ENV['TIMELOG_FILEPATH']);
        $rep = new \gtimelogphp\MonthReport($logfile);
        $rep2 = $rep->report();
        return $rep2;
    }

}
