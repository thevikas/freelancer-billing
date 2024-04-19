<?php

namespace app\controllers\api;

use app\models\Project as ProjectModal;
use app\models\PomodoroTask;
use stdClass;
use Yii;
use yii\filters\Cors;

require_once __DIR__ . '/../../Project.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../Report.php';


function sortByLastTime($a, $b)
{
    return $a['last_time'] <=> $b['last_time'];
}


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
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'), Yii::$app->params['envFile']);
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
        $parsed_logs = json_decode(file_get_contents($_ENV['TIMELOG_GITREPO'] . "/cache/{$firstDay}_parsed.json"), true);

        usort($parsed_logs, [$this, 'sortByLastTime']);

        // Initialize an array to store unique entries
        $filteredData = [];

        // Loop through the sorted array and filter out duplicates based on project and task
        foreach ($parsed_logs as $entry)
        {
            $key = $entry['project'] . '-' . $entry['task'];
            if (!isset($filteredData[$key]))
            {
                $filteredData[$key] = $entry;
            }
            else
            {
                // Combine spent_time_secs and update last_time if needed
                $filteredData[$key]['spent_time_secs'] += $entry['spent_time_secs'];
                $filteredData[$key]['last_time'] = max($filteredData[$key]['last_time'], $entry['last_time']);
            }
        }

        // Sort the filtered array by last_time again
        usort($filteredData, [$this, 'sortByLastTime']);

        //check if tomorodo is running, append that task
        $cache = Yii::$app->cache;
        $pomodoro = $cache->get('pomodoro');
        if($pomodoro && $pomodoro->spent_time_secs > $pomodoro->duration)
        {
            $pomodoro = null;
        }        
        if ($pomodoro)
        {
            //$ar = $pomodoro;
            //$ar['spent_time_secs'] = $pomodoro->spent_time_secs;            
            $filteredData[] = [
                'project' => $pomodoro->project,
                'task' => $pomodoro->task,
                'last_time' => $pomodoro->last_time,
                'spent_time_secs' => $pomodoro->spent_time_secs,
                'status' => $pomodoro->status
            ];            
        }
        $filteredData = array_reverse($filteredData);
        return $filteredData;
    }

    /**
     * Post log in timesheet, data is raw gtimelog format
     *
     * @param [type] $log
     * @return void
     */
    public function actionIndex($log = null)
    {
        $proj = new \gtimelogphp\Project("");

        //read POSTed JSON data
        $data = json_decode(Yii::$app->request->getRawBody(), true);
        if(!empty($data['log']))
            $log = $log ?? $data['log'];
        
        $proj->logNow($log, $this->logfile, [
            "", $log
        ], false, $_ENV['TIMELOG_PCNAME'], true);

        //redirect to last
        return $this->actionLast();
    }

    public function actionAway()
    {
        return $this->actionIndex("away");
    }

    public function actionLast()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $proj = new \gtimelogphp\Project("");
        $lastInfo = $proj->logNow("", $this->logfile, null, false, $_ENV['TIMELOG_PCNAME'], true);
        $rep = new \gtimelogphp\MonthReport($this->logfile);
        $rep2 = $rep->report('', 2);

        $projName = $lastInfo['project'];

        if (empty($rep2[$projName]['datestasks'][date('Y-m-d')]))
        {
            $lastInfo['duration'] = 0;
            $lastInfo['status'] = 'stopped';
            return $lastInfo;
        }

        $taskInfo = $rep2[$projName]['datestasks'][date('Y-m-d')][$lastInfo['task']];
        $lastInfo['duration'] = $rep2[$projName]['datestasks'][date('Y-m-d')][$lastInfo['task']];
        $lastInfo['status'] = 'running';
        $lastInfo['status'] = 'running';
        return $lastInfo;
    }

    public function actionPomodoro()
    {
        $this->actionAway();
        //store pomodoro flag in cache with datetime
        $cache = Yii::$app->cache;
        $pomodoro = $cache->get('pomodoro');
        if($pomodoro && $pomodoro->spent_time_secs > $pomodoro->duration)
        {
            $pomodoro = null;
        }        
        if (!$pomodoro)
        {        
            $pomodoro = new PomodoroTask();
            $pomodoro->status = 'running';
            $pomodoro->project = 'Pomodoro';
            $pomodoro->task = 'Pomodoro';
            $pomodoro->duration = 25 * 60;  
            $pomodoro->last_time = time();
        }
                
        $cache = Yii::$app->cache;
        $cache->set('pomodoro', $pomodoro, 25 * 60);        
        return $pomodoro;
    }

    // Define a function to compare elements based on last_time
    function sortByLastTime($a, $b)
    {
        return $a['last_time'] <=> $b['last_time'];
    }
}
