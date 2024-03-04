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
    }

    public function actionIndex()
    {
        $proj = new Project();

        //create a array of projects and stats
        $stats = [];
        foreach ($proj->cache['summary']['BillableProjects'] as $projname => $data)
        {
            $stats[$projname] = $data['stats'];
        }

        $stats['summary'] = $proj->cache['summary'];
        unset($stats['summary']['BillableProjects']);

        //set json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return $stats;
    }

    public function actionGet($id)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();
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
