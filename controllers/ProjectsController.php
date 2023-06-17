<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProjectsController implements the CRUD actions for Project model.
 */
class ProjectsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Project models.
     * @return mixed
     */
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

        $dataProvider = new ArrayDataProvider([
            'allModels' => $stats,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['Name', 'Hours', 'Income', 'Dated'],
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'summary' => $proj->cache['summary'],
        ]);
    }

    /**
     * Displays a single Project model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($projcode)
    {        
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'));
        $dotenv->load();
        $proj = new Project([
            'project' => $projcode
        ]);

        $tasks  = $proj->cache['summary']['BillableProjects'][$projcode];
        unset($tasks['name']);
        unset($tasks['Income']);
        unset($tasks['Total']);

        $total = 0;

        $result = array();

        foreach ($tasks['times'] as $key => $value)
        {

            if ('Total' == $key || 'Dated' == $key)
                continue;

            $ss = explode(':', $value . ":0");
            $mins = $ss[0] * 60 + $ss[1];
            $total += $mins;

            $result[] = array(
                'task' => $key,
                'times' => $value,
                'projcode' => $projcode,
                'spent' => round($mins / 60, 2),
            );
        }

        $result[] = array(
            'task' => 'Total1',
            'spent' => round($tasks['times']['Total'],2),
        );

        $result[] = array(
            'task' => 'Total2',
            'spent' => round($total/60,2),
        );

        $proj->data['current']['hours'] = round($total/60,2);
        $proj->data['current']['amount'] = $proj->data['current']['hours'] * $proj->data['per_hour'];

        $dataProvider = new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => ['task', 'spent'],
            ],
        ]);

        return $this->render('view', [
            'data' => $result,
            'dataProvider' => $dataProvider,
            'projcode' => $projcode,
            'proj' => $proj,
        ]);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Project();

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Project model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null)
        {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
