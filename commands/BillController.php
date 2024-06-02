<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BillController extends Controller
{
    /* @var string CCSV file */
    public $csvfile;
    /* @var string Project code */
    public $project;
    /* @var string Invoice ID */
    public $id;

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function options($actionID)
    {
        return ['csvfile','project','id'];
    }

    public function actionAddTimesheet()
    {
        if(empty($this->csvfile) || empty($this->project) || empty($this->id)){
            echo "Please provide csvfile, project and id\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $file = fopen($this->csvfile, 'r');
        $header = fgetcsv($file);
        if(strtolower($header[0]) != 'task' || strtolower($header[1]) != 'time'){
            echo "Invalid CSV file\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $rows = [];
        while($row = fgetcsv($file)){
            $row[1] += 0;
            $rows[] = $row;//array_combine($header, $row);
        }

        $invoice_json_dir = \Yii::getAlias('@app/data');
        //$invoice_json_file = $invoice_json_dir . DIRECTORY_SEPARATOR . $this->id . '.json';
        $invoice_json_file = sprintf('%s/%02d-%s.json', $invoice_json_dir, $this->id, $this->project);

        if(!file_exists($invoice_json_file)){
            echo "Invoice file not found\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $obj = json_decode(file_get_contents($invoice_json_file), true);

        if(!empty($obj["extra-timesheet"]))
        {
            echo "Extra timesheet already added\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $obj["extra-timesheet"] = $rows;

        file_put_contents($invoice_json_file, json_encode($obj, JSON_PRETTY_PRINT));
    }
}
