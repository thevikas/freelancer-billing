<?php

namespace app\controllers;

use Yii;
use app\models\Bill;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;

/**
 * BillsController implements the CRUD actions for Bill model.
 */
class BillsController extends Controller
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
     * Lists all Bill models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => Bill::loadfiles()
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Bill model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $bills = Bill::loadfiles();
        $clients = json_decode(file_get_contents(Yii::getAlias('@app/config/client-projects.json')),true);
        $invoice = $bills[$id];
        $project = $clients['projects'][$bills[$id]['client']];
        if(empty($project['layout']))
            $this->layout = 'blue-invoice';
        else
            $this->layout = $project['layout'];

        if(empty($invoice['total']))
            $invoice['total'] = $invoice['hours'] * $project['per_hour'];
        $invoice['items'][0] = [
            'name' => 'Software development',
            'price' => $project['per_hour'],
            'quantity' => $invoice['hours'] ?? "",
            'amount' => $invoice['total']
        ];
        if(!empty($invoice['extra_items']))
        {
            foreach($invoice['extra_items'] as &$item)
            {

                $item['quantity'] = $item['price'] = " ";
                if(!empty($item['overtime']))
                {
                    $item['amount'] = ($item['overtime']*$project['overtime_per_hour']);
                    $item['price'] = $project['overtime_per_hour'];
                    $item['quantity'] = $item['overtime'];
                }

                if(!empty($item['amount']))
                    $invoice['total'] += $item['amount'];
                else
                    throw new Exception("Do not know how to include extra item in calc");

                $invoice['items'][] = $item;
            }
        }
        $invoice['total_inr'] = round($invoice['total'] * $clients['ccy'][$project['ccy']]);
        return $this->render('view', [
            'id_invoice' => $id,
            'invoice' => $invoice,
            'project' => $project
        ]);
    }

    /**
     * Creates a new Bill model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Bill();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_bill]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Bill model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_bill]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Bill model.
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
     * Finds the Bill model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Bill the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bill::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
