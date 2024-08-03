<?php

namespace app\controllers;

use app\models\Uploads;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UploadsController implements the CRUD actions for Uploads model.
 */
class UploadsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Uploads models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Uploads::find()->orderBy(['id_upload' => SORT_DESC]),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id_upload' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Uploads model.
     * @param int $id_upload id_upload
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id_upload)
    {
        $model = $this->findModel($id_upload);

        $fields = [];//$model->parseFields();

        return $this->render('view', [
            'model' => $model,
            'fields' => $fields
        ]);
    }

    /**
     * Creates a new Uploads model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Uploads();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id_upload' => $model->id_upload]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Uploads model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id_upload id_upload
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id_upload)
    {
        $model = $this->findModel($id_upload);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id_upload' => $model->id_upload]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Uploads model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id_upload id_upload
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id_upload)
    {
        $this->findModel($id_upload)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Uploads model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id_upload id_upload
     * @return Uploads the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id_upload)
    {
        if (($model = Uploads::findOne(['id_upload' => $id_upload])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionTs($id_invoice)
    {
        $model = new Uploads();
        $model->id_invoice = $id_invoice;
        $model->filetype = 'text/csv';
        $ts = date('YmdHis');

        if ($this->request->isPost) 
        {   
            if($_POST['uploadtype'] == 'file')
            {
                $csv_file = \yii\web\UploadedFile::getInstance($model, 'file');
                $model->filename = $ts . "-" . $csv_file->baseName . '.' . $csv_file->extension;
                //Yii::getAlias('@webuploads/'
                //Yii::getAlias('@webuploads/' . $model->filename)
                $model->filepath = Yii::getAlias('@uploads/') . $ts . "-" . $csv_file->baseName . '.' . $csv_file->extension;
                //save csv file
                $csv_file->saveAs($model->filepath);
            }
            else if($_POST['uploadtype'] == 'url')
            {
                $model->filename = $ts . "-url.csv";
                $model->filepath = \Yii::getAlias(\Yii::$app->params['uploads_dir']) . $ts . "-url.csv";
                $url = $_POST['Uploads']['url'];
                $content = file_get_contents($url);
                file_put_contents($model->filepath, $content);
            }
            else if($_POST['uploadtype'] == 'text')
            {
                $model->filename = $ts . "-text.csv";
                $model->filepath = \Yii::getAlias(\Yii::$app->params['uploads_dir']) . $ts . "-text.csv";
                $content = $_POST['Uploads']['text'];
                file_put_contents($model->filepath, $content);
            }
            else
            {
                throw new \yii\base\Exception('Invalid upload type');
            }            
            if($model->filetype != 'text/csv')
                throw new \yii\base\Exception('Invalid file type');
            $model->filetype = $_REQUEST['Uploads']['filetype'];
            $model->id_user = \Yii::$app->user->id_upload ?? 0;
            $model->created = date('Y-m-d H:i:s');            

            if($model->save())
                return $this->redirect(['process', 'id_upload' => $model->id_upload]);
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    public function actionProcess($id_upload)
    {
        $model = $this->findModel($id_upload);
        if($model->processFile())
        {            
            return $this->redirect(['bills/' . '/index', 'id_upload' => $model->id_upload]);
        }
        
        return $this->render('process', [
            'model' => $model,
            //'fields' => $fields
        ]);
    }

}
