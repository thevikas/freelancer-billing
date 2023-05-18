<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property int $name
 */
class Project extends Model
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }
}
