<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * A bill stored in a json file
 *
 */
class Bill extends Model
{
    public $client;
    public $hours;
    public $json;

    static function loadfiles()
    {
        $bills = [];
        $DIR = opendir($startdir = Yii::getAlias('@app/data'));
        while($file = readdir($DIR))
        {
            if($file[0] == '.')
                continue;
            $mats = [];
            if(!preg_match('/^(?<id_invoice>\d+)\-(?<clientcode>[\-\w]+)\.json/',$file,$mats))
                throw new \Exception("Invoice file $file name could not parts");
            $jsonfile = $startdir . '/' . $file;
            $bill = json_decode(file_get_contents($jsonfile),true);
            $bill['jsonfile'] = $jsonfile;
            $bill['id_invoice'] = $mats['id_invoice'];
            $bills[$mats['id_invoice']] = $bill;
        }
        closedir($DIR);

        uasort($bills, function($a,$b) {
            $ad = strtotime($a['dated']);
            $bd = strtotime($b['dated']);
            if ($ad == $bd) {
                return ($a['id_invoice'] < $b['id_invoice']) ? 1 : -1;
            }
            return ($ad < $bd) ? 1 : -1;
        });

        return $bills;
    }
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['client', 'hours','json'], 'required'],
        ];
    }

    public function saveExtra($id,$extra_ts,$extra_hours)
    {
        $bills = self::loadfiles();
        $bill = $bills[$id];
        $bill['extra-timesheet'] = $extra_ts;
        $bill['hours'] += $extra_hours;
        $jsonfile = $bill['jsonfile'];
        unset($bill['jsonfile']);
        file_put_contents($jsonfile,json_encode($bill,JSON_PRETTY_PRINT));
    }
}
