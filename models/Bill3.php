<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bill".
 *
 * @property int $id_bill
 * @property int $id_client
 * @property string $dated
 * @property float $hours
 */
class Bill3 extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_client', 'dated', 'hours'], 'required'],
            [['id_client'], 'integer'],
            [['dated'], 'safe'],
            [['hours'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_bill' => 'Id Bill',
            'id_client' => 'Id Client',
            'dated' => 'Dated',
            'hours' => 'Hours',
        ];
    }
}
