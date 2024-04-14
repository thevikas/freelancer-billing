<?php

namespace app\controllers\api;

use app\models\Project as ProjectModal;
use stdClass;
use Yii;
use yii\filters\Cors;

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
        date_default_timezone_set('Asia/Kolkata');
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'),Yii::$app->params['envFile']);
        $dotenv->load();       
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->logfile = Yii::getAlias($_ENV['TIMELOG_FILEPATH']);
        //ignore csrf
        Yii::$app->request->enableCsrfValidation = false;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Remove the auth filter or adjust as per your security policies
        unset($behaviors['authenticator']);

        // Add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // Restrict access to
                'Origin' => ['*'], // Adjust if necessary to your client-side URL
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'OPTIONS'], // Adjust request methods as needed
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Allow-Headers' => ['*'], // Adjust required headers
            ],
        ];

        return $behaviors;
    }

    public function actionToday()
    {
        $firstDay = date('Y-m-01');
        $today = date('Y-m-d');
        //TIMELOG_GITREPO
        //load RATES_JSON_FILE
        $todaylogs = [];
        $parsed_logs = json_decode(file_get_contents($_ENV['TIMELOG_GITREPO'] . "/cache/{$firstDay}_parsed.json"),true);
        foreach ($parsed_logs as $log)
        {
            $dt = date('Y-m-d',$log['last_time']);
            if ($dt == $today)
            {
                $todaylogs[] = $log;
            }
        }

        foreach ($parsed_logs as $log)
        {
            $todaylogs[] = $log;
        }

        return $todaylogs;
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
