<?php
namespace app\components;

use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        // Set path aliases here
        Yii::setAlias('@webuploads', '@web' . DIRECTORY_SEPARATOR . Yii::$app->params['uploads_dir']);
        Yii::setAlias('@uploads', '@webroot' . DIRECTORY_SEPARATOR . Yii::$app->params['uploads_dir']);
    }
}
