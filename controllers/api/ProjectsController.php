<?php

namespace app\controllers\api;

use app\models\Project;
use Yii;

/**
 * Rerturns all data in JSON
 */
class ProjectsController extends \yii\web\Controller
{
    public function init()
    {
        parent::init(); 
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();        
        //set json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function actionIndex()
    {
        $proj = new Project();

        //create a array of projects and stats
        $stats = [];
        $summary = ['EstimatedTotalHours' => 0];
        foreach ($proj->cache['summary']['BillableProjects'] as $projname => $data)
        {
            $stats[$projname] = $data['stats'];
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
