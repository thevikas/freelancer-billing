<?php

namespace app\controllers\api;

use app\models\Project;
use Yii;

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
    public function actionIndex($log)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();
        $proj = new Project();

        //set json header
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function actionLast()
    {
        
    }

}
