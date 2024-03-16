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

    public $logfile = "";
    public function init()
    {
        parent::init();
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'),Yii::$app->params['envFile']);
        $dotenv->load();       
        $this->logfile = Yii::getAlias($_ENV['TIMELOG_FILEPATH']);
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
        
        $proj->logNow($log,$this->logfile,[
            "",$log
        ],false,$_ENV['TIMELOG_PCNAME'],true );

        //redirect to last
        return $this->actionLast();
    }

    public function actionLast()
    {
        //return json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;        

        $proj = new \gtimelogphp\Project("");
        $lastInfo = $proj->logNow("",$this->logfile,null,false,$_ENV['TIMELOG_PCNAME'],true );
        $rep = new \gtimelogphp\MonthReport($this->logfile);
        $rep2 = $rep->report('',2);

        $projName = $lastInfo['project'];
        $taskInfo = $rep2[$projName]['datestasks'][date('Y-m-d')][$lastInfo['task']];
        $lastInfo['duration'] = $rep2[$projName]['datestasks'][date('Y-m-d')][$lastInfo['task']];
        $lastInfo['status'] = 'running';
        $lastInfo['status'] = 'running';
        return $lastInfo;
    }

}
