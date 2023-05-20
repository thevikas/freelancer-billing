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
    public $cache;
    
    function init()
    {
        parent::init();
        //find cache file
        $FirstDayOfMonth = strtotime(date('Y-m-01'));
        $cacheJsonFileName =$_ENV['TIMELOG_GITREPO'] . '/' . date('Y-m-d',$FirstDayOfMonth) . ".json";
        if(!file_exists($cacheJsonFileName))
            $jsondata = $this->updateCache();
        //verify it is todays date
        else
        {
            $jsondata = json_decode(file_get_contents($cacheJsonFileName),true);
            if($jsondata['dated'] != date('Y-m-d'))
                $jsondata = $this->updateCache();
        }
        $this->cache = $jsondata;
    }

    /**
     * Update cache and return latest json parsed data
     *
     * @return array parsed data
     */
    function updateCache()
    {
        $cmd = Yii::getAlias('@app') . "/gt.php --cache -m this_month";
        echo "Running $cmd\n";
        $output = [];
        $ret = 0;
        $ret = system("php81 $cmd");
        if($ret)
            die("Error running $cmd");
        $FirstDayOfMonth = strtotime(date('Y-m-01'));
        $cacheJsonFileName =$_ENV['TIMELOG_GITREPO'] . '/' . date('Y-m-d',$FirstDayOfMonth) . ".json";
        return json_decode(file_get_contents($cacheJsonFileName),true);
    }
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
