<?php
namespace gtimelogphp;

/**
 * Organizes data so bill for some project can be made
 */
class Bill
{
    /**
     * @var array
     */
    public $data = [];

    public $rates = [];
    /**
     * @param $report_data
     */
    public function __construct($report_data)
    {
        $this->data = $report_data;
        $json = \file_get_contents($_ENV['RATES_JSON_FILE']);
        $this->rates = json_decode($json, true);
        $this->syncAliases();
    }

    public static function getNextInvoiceNumber()
    {
        $num = 0;
        $DIR = opendir($_ENV['BILLS_JSON_DIR']);
        while ($file = readdir($DIR))
        {
            if ($file[0] == '.')
            {
                continue;
            }
            $mats = [];
            if (preg_match('/(?<num>\d+)/', $file, $mats))
            {
                if ($mats['num'] > $num)
                {
                    $num = $mats['num'];
                }

            }
        }
        closedir($DIR);
        return $num + 1;
    }

    public function syncAliases()
    {
        foreach ($this->rates['projects'] as $name1 => $proj)
        {
            if (!empty($proj['aliases']))
            {
                foreach ($proj['aliases'] as $alias)
                {
                    #if (empty($this->rates['projects'][$alias]))
                    {
                        $this->rates['projects'][$alias] = $proj;
                        $this->rates['projects'][$alias]['aliases'] = [];
                    }
                }
            }
        }
    }

    public function saveJson($rep, $project_name)
    {
        $inum = self::getNextInvoiceNumber();

        //check for prev 10 inum json files 
        $ictr = $inum-10;
        $resuming = false;
        for($i=0; $i<10; $i++)
        {
            $json_file = $_ENV['BILLS_JSON_DIR'] . "/" . $ictr . "-" . $project_name . ".json";
            //load json and verify dated
            if (file_exists($json_file))
            {
                $json = json_decode(file_get_contents($json_file), true);
                if($json['dated'] == date('Y-m-d'))
                {
                    $inum = $ictr;
                    echo "Using $json_file...\n";
                    $resuming = true;
                    break;
                }
            }
            $ictr++;
        }

        $json_file = $_ENV['BILLS_JSON_DIR'] . "/" . $inum . "-" . $project_name . ".json";
        if (!$resuming && file_exists($json_file))
        {
            die("$json_file already exists");
        }

        echo "Writing $json_file...\n";

        /*
        {
        "client": "care4life-btc",
        "hours": 82,
        "dated": "2023-01-01",
        }
         */
        $json = [
            'hours'  => $rep['hours'],
            'client' => $project_name,
            'dated'  => date('Y-m-d'),
        ];
        file_put_contents($json_file, json_encode($json, JSON_PRETTY_PRINT));
        return $inum;
    }

    public function printPDF($invoiceNum,$projcode)
    {
        $cmd = "node screenshot.js $invoiceNum $projcode";
        system($cmd);
    }

    /**
     * Checks the report data
     * Loads the billing info
     * Calculates the bill from totals
     */
    public function report()
    {
        $rep = [];
        $totalEarning = 0;
        $base_ccy = '';
        foreach ($this->rates['ccy'] as $ccy => $val)
        {
            if (1 == $val)
            {
                $base_ccy = $ccy;
            }

        }

        #print_r($this->data);
        #print_r($this->rates);
        foreach ($this->data as $proj => &$items)
        {
            $rep[$proj]['default'] = false;
            if (isset($this->rates['projects'][$proj]))
            {
                $rate = $this->rates['projects'][$proj];
            }
            else
            {
                $rate = $this->rates['projects']['default'];
                $rep[$proj]['default'] = true;
            }
            $quantity = $items['Total'];
            $items['TotalAmount'] = $quantity * $rate['per_hour'];
            $rep[$proj]['hours'] = $quantity;
            $rep[$proj]['TotalAmount_' . $rate['ccy']] = $items['TotalAmount'];
            $rep[$proj]['TotalAmount_' . $base_ccy] = $items['TotalAmount'] * $this->rates['ccy'][$rate['ccy']];
            if (!$rep[$proj]['default'])
            {
                $totalEarning += $rep[$proj]['TotalAmount_' . $base_ccy];
            }

        }
        $rep['TotalEarning'] = $totalEarning;
        return $rep;
    }
}
