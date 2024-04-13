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

    public $project;

    public $data;

    function init()
    {
        parent::init();

        if (!empty($this->project))
        {
            $ratesJson = json_decode(file_get_contents($_ENV['RATES_JSON_FILE']), true);
            $this->data = $ratesJson['projects'][$this->project];
        }
        //find cache file
        $FirstDayOfMonth = strtotime(date('Y-m-01'));
        $dmy = date('Y-m-d', $FirstDayOfMonth);
        $cacheJsonFileName = $_ENV['TIMELOG_GITREPO'] . '/cache/' . $dmy . ".json";
        Yii::info("cacheJsonFileName=$cacheJsonFileName");
        if (!file_exists($cacheJsonFileName))
            $jsondata = $this->updateCache();
        //verify it is todays date
        else
        {
            $jsondata = json_decode(file_get_contents($cacheJsonFileName), true);
            if (date('Y-m-d', strtotime($jsondata['dated'])) != date('Y-m-d'))
                return $this->updateCache();
        }
        $this->cache = $jsondata;
    }

    public function loadCache($month)
    {
        $FirstDayOfMonth = strtotime(date("$month-01"));
        $dmy = date('Y-m-d', $FirstDayOfMonth);
        $cacheJsonFileName = $_ENV['TIMELOG_GITREPO'] . '/cache/' . $dmy . ".json";
        Yii::info("cacheJsonFileName=$cacheJsonFileName");
        if (!file_exists($cacheJsonFileName))
        {
            $this->updateCache($month);
            return $this->loadCache($month);
        } 
        //verify it is todays date
        $data = json_decode(file_get_contents($cacheJsonFileName), true);    
        return $data;
    }

    /**
     * Update cache and return latest json parsed data
     *
     * @return array parsed data
     */
    function updateCache($month = null)
    {
        $mon1 = $month ?? 'this_month';
        $cmd = Yii::getAlias('@app') . "/gt.php --cache -m $mon1";
        Yii::info("Running $cmd");
        #echo "Running $cmd\n";
        $output = [];
        $ret = 0;
        //run command and see output
        $cmd = $_ENV['PHP_BIN'] . " $cmd";
        $last_line = exec($cmd, $output, $ret);
        #echo "output=" . print_r($output,true);
        if ($ret != 0)
            die("Error running $cmd");
        $FirstDayOfMonth = strtotime(date('Y-m-01'));
        $cacheJsonFileName = $_ENV['TIMELOG_GITREPO'] . '/cache/' . date('Y-m-d', $FirstDayOfMonth) . ".json";
        if(!file_exists($cacheJsonFileName))
            throw new \Exception("$cacheJsonFileName not found");
        return $this->cache = json_decode(file_get_contents($cacheJsonFileName), true);
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
