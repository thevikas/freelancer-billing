<?php

namespace app\controllers\api;

use app\models\Project;
use Yii;

/**
 * Rerturns all data in JSON
 */
class ProjectsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();
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

}
