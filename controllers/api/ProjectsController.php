<?php

namespace app\controllers\api;

use app\models\Project;
use Yii;
use yii\filters\Cors;

require_once __DIR__ . '/../../Project.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../Report.php';

/**
 * Rerturns all data in JSON
 */
class ProjectsController extends \yii\web\Controller
{
    public $logfile = "";
    public function init()
    {
        parent::init(); 
        date_default_timezone_set('Asia/Kolkata');
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();        
        //set json header
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

    public function actionIndex($showall=0)
    {
        $proj = new Project();

        $rep = new \gtimelogphp\MonthReport($this->logfile);
        $rep2 = $rep->report('',2);

        //create a array of projects and stats
        $stats = 1 == $showall ? $rep2 : [];
        $summary = ['EstimatedTotalHours' => 0];
        foreach ($proj->cache['summary']['BillableProjects'] as $projname => $data)
        {
            $stats[$projname] = $data['stats'];
            $stats[$projname]['recent'] = $rep2[$projname]['recent'];
            $summary['EstimatedTotalHours'] = $summary['EstimatedTotalHours'] + $data['stats']['EstimatedTotalHours'];
        }

        $stats['summary'] = array_merge($proj->cache['summary'],$summary);
        $stats['summary']['name'] = "Summary";
        $stats['summary']['Dated'] = date('Y-m-d H:i:s');
        //$stats['summary']['EstimatedTotalHours'] = 
        unset($stats['summary']['BillableProjects']);

        return $stats;
    }

    public function actionTasks($id,$month="")
    {
        $proj = new Project('');

        $data = $proj->loadCache($month);
        $data2 = $data['report_data'][$id];
        return $data2['Tasks'];
    }

    public function actionMeta($project = "",$showall = "")
    {
        //load RATES_JSON_FILE
        $json = json_decode(file_get_contents($_ENV['RATES_JSON_FILE']),true);
        $showall = 1 == $showall? true : false;
        $rt = [];
        foreach($json['projects'] as $proj => $data)
        {
            if(empty($data['billingactive']))
                continue;
            if(empty($project) || $project == $proj)
                $rt[$proj] = [
                    'subtitle' => $data['subtitle'] ?? $proj,
                    'imgSrc' => $data['imgSrc'] ?? ''
                ];
        }

        return $rt;
    }

    public function actionGet($id)
    {
        $proj = new Project();

        //set json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return $proj->cache['summary']['BillableProjects'][$id];
    }

    /**
     * Refresh 
     *
     * @return void
     */
    public function actionRefresh()
    {
        $proj = new Project('');
        $proj->updateCache();
        return $this->actionIndex();
    }

}
