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
            echo "Please provide csvfile, project (code) and id (invoice id)\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $file = fopen($this->csvfile, 'r');
        $header = fgetcsv($file);
        if(strtolower($header[0]) != 'task' || strtolower($header[1]) != 'time'){
            echo "Invalid CSV file, need [task,time] fields\n";
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
            echo "Invoice file [$invoice_json_file] not found\n";
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

    public function actionSign($pdf_filepath = "")
    {
        $invoice_json_dir = \Yii::getAlias('@app/data');
        $mats = [];
        if(!empty($pdf_filepath))
        {
            if(!preg_match('/Invoice\-(?<id_invoice>\d+)\-(?<clientcode>[\-\w]+)\.pdf/', $pdf_filepath, $mats))
            {
                echo "Invalid PDF file name\n";
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $this->id = $mats['id_invoice'];
            $this->project = $mats['clientcode'];
        }
        else
        {
            if(empty($this->project) || empty($this->id)){
                echo "Please provide project (code) and id (invoice id)\n";
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        $invoice_json_file = sprintf('%s/%02d-%s.json', $invoice_json_dir, $this->id, $this->project);

        if(!file_exists($invoice_json_file)){
            echo "Invoice file [$invoice_json_file] not found\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $obj = json_decode(file_get_contents($invoice_json_file), true);

        if(!empty($obj["sha256"]))
        {
            echo "Invoice already signed\n";
            return ExitCode::OK;
        }

        $obj["sha256"] = hash_file('sha256', $invoice_json_file);

        $invoice_pdf_dir = \Yii::getAlias('@runtime/pdf');
        $invoice_pdf_file = sprintf('%s/Invoice-%02d-%s.pdf', $invoice_pdf_dir, $this->id, $this->project);

        if(!file_exists($invoice_pdf_file)){
            echo "Invoice PDF file [$invoice_pdf_file] not found\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $obj["sha256-pdf"] = hash_file('sha256', $invoice_pdf_file);
        
        $gpg_sign = "gpg --armor --detach-sign --output %s.asc %s";
        $gpg_sign = sprintf($gpg_sign, $invoice_pdf_file, $invoice_pdf_file);
        exec($gpg_sign);

        //store sign in json
        $obj["gpg-sign"] = file_get_contents($invoice_pdf_file . '.asc');

        file_put_contents($invoice_json_file, json_encode($obj, JSON_PRETTY_PRINT));
    }

    public function actionVerify($pdf_filepath)
    {
        if(empty($pdf_filepath)){
            echo "Please provide PDF file path\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $mats = [];
        if(!preg_match('/Invoice\-(?<id_invoice>\d+)\-(?<clientcode>[\-\w]+)\.pdf/', $pdf_filepath, $mats))
        {
            echo "Invalid PDF file name\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $pdf_sha256 = hash_file('sha256', $pdf_filepath);

        $invoice_json_dir = \Yii::getAlias('@app/data');
        $invoice_json_file = sprintf('%s/%02d-%s.json', $invoice_json_dir, $mats['id_invoice'], $mats['clientcode']);
        
        if(!file_exists($invoice_json_file)){
            echo "Invoice file [$invoice_json_file] not found\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $obj = json_decode(file_get_contents($invoice_json_file), true);

        if($pdf_sha256 != $obj["sha256-pdf"]){
            echo "PDF file SHA256 does not match\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $gpg_sig = $obj["gpg-sign"];
        //gpg with STDIN
        $gpg_verify = "echo '$gpg_sig' | gpg --verify - %s";
        $gpg_verify = sprintf($gpg_verify, $pdf_filepath);
        exec($gpg_verify, $output, $return_var);

        //echo sha256
        echo "Sha256: " . $pdf_sha256 . "\n";
    }
}
