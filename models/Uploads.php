<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "uploads".
 *
 * @property int $id_upload
 * @property int $id_invoice
 * @property int $id_user
 * @property string $filename
 * @property string $filepath
 * @property string $created
 * @property string $filetype
 * @property int $datasource
 */
class Uploads extends \yii\db\ActiveRecord
{
    /** @var UploadedFile Temporary variable to hold the uploaded file */
    var $file;
    //'NEW', 'PROCESSING', 'PROCESSED', 'ERROR')
    const STATUS_NEW = 'NEW';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_PROCESSED = 'PROCESSED';
    const STATUS_ERROR = 'ERROR';    

    var $text;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'uploads';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_invoice', 'id_user', 'filename', 'filepath', 'created'], 'required'],
            [['id_invoice', 'id_user'], 'integer'],
            [['created'], 'safe'],
            [['filetype'], 'string', 'max' => 20],
            //status enaum
            [['status'], 'in', 'range' => [self::STATUS_NEW, self::STATUS_PROCESSING, self::STATUS_PROCESSED, self::STATUS_ERROR]],
            [['filename', 'url_json', 'filepath'], 'string', 'max' => 255],
            [['text'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_upload' => 'id_upload',
            'id_invoice' => 'id Datasource',
            'id_user' => 'id_upload User',
            'filename' => 'Filename',
            'filepath' => 'Filepath',
            'created' => 'Created',
        ];
    }

    /*public function getDatasource()
    {
        return $this->hasOne(DataSource::class, ['id_invoice' => 'id_invoice']);
    }*/

    public function getFiletypeOptions()
    {
        return [
            'text/csv' => 'CSV',
            'application/json' => 'JSON',
            'text/xml' => 'XML',
            'gsheet' => 'Google Sheet',
        ];
    }

    /**
     * Will load the csv file
     * based on data source, load the model
     * insert each line into the model 
     *
     * @return void
     */
    public function processFile()
    {
        $file = fopen($this->filepath, 'r');
        $header = fgetcsv($file);
        $lc = 0;
        $bill = new Bill();
        //start transaction
        $transaction = Yii::$app->db->beginTransaction();
        $ts_row = [];
        $extra_hours = 0;
        while ($row = fgetcsv($file))
        {
            $lc++;            
            $data = array_combine($header, $row);    
            $ts_row[] = [ $row[2], $row[4] ];
            $extra_hours += $row[4];
        }

        $bill->saveExtra($this->id_invoice, $ts_row, $extra_hours);
        
        $transaction->commit();

        fclose($file);
        return true;
    }

    /**
     * Will return the field name with space replaced with underscore
     *
     * @param string $field
     * @return string
     */
    private function _2space($field)
    {
        return str_replace('_', ' ', $field);
    }
}
