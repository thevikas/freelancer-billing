#!/usr/bin/env php
<?php
    require __DIR__ . "/vendor/autoload.php";
    require_once 'functions.php';

    $logfile = getenv('HOME') . '/.local/share/gtimelog/timelog.txt';
    
    $hello_cmd = new Commando\Command();

    $hello_cmd->option()
        ->describedAs('Log entry');


    // Define a boolean flag "-c" aka "--capitalize"
    $hello_cmd->option('r')
        ->aka('report')
        ->describedAs('Report')
        ->boolean();

    $hello_cmd->option('b')
        ->aka('bill')
        ->describedAs('Bill')
        ->boolean();

    $hello_cmd->option('e')
        ->aka('earning')
        ->describedAs('Just report this months earnings')
        ->boolean();

    // Define a boolean flag "-c" aka "--capitalize"
    $hello_cmd->option('m')
        ->aka('month')
        ->describedAs('Report for Month (last_month, this_month or YYYY-MM0)')
        ->default("last_month");

    date_default_timezone_set('Asia/Kolkata');
    $all_lines = [];

    if (!file_exists($logfile))
    {
        fputs(STDERR, "$logfile: File not found\n");
        return -1;
    }
    $away  = false;
    $argv2 = $argv;

    if($hello_cmd['report'] || $hello_cmd['bill'] || $hello_cmd['earning'])
    {
        $rep = new gtimelogphp\MonthReport($logfile);
        if('last_month' == $hello_cmd['month'])
            $FirstDayOfMonth      = strtotime(date('Y-m-01',strtotime("-1 month")));
        else if('this_month' == $hello_cmd['month'])
            $FirstDayOfMonth      = strtotime(date('Y-m-01'));
        else
            $FirstDayOfMonth      = strtotime(date('Y-m-01',strtotime($hello_cmd['month'])));
        #echo "FirstDayOfMonth = " . date('Y-m-d',$FirstDayOfMonth) . "\n";
        $report_data = $rep->report($FirstDayOfMonth);
        if($hello_cmd['report'])
        {
            print_r($report_data);
        }
        else if($hello_cmd['bill'] || $hello_cmd['earning'])
        {
            $bill = new gtimelogphp\Bill($report_data);
            $rep = $bill->report();
            if($hello_cmd['earning'])
                echo sprintf("%d",round($rep['TotalEarning'])) . "\n";
            else
                print_r($rep);
;        }
        return;
    }

    $fullarg = $hello_cmd[0];
    #Reading
    $L = fopen($logfile, 'r');
    fseek($L, -200, SEEK_END);
    $last_dt = $last_time = $lc = 0;
    iterate($L);

    fclose($L);

    if (!empty($argv[1]))
    {
        if ('last' == $argv[1])
        {
            $fullarg = $last_comment;
        }
        else if ('away' == $argv[1])
        {
            $away    = true;
            $fullarg = $last_comment;
        }
    }

    if (empty($argv[1])) //if not arg given, we just show time spent doing the last item
    {
        $mm = difftime();
        echo "$mm: $last_comment\n";
        return -1;
    }

    #Writing
    $L = fopen($logfile, 'a');
    #fseek($L, -200, SEEK_END);

    $last_dt    = date('Y-m-d', $last_time);
    $today_date = date('Y-m-d');
    if (strstr($last_dt, $today_date) === false) //Not same day
    {
        fputs($L, "\n");
    }

    //mark a time period as away between last and this and resume the work
    if ($away)
    {
        $newline = sprintf('%s: away **', date('Y-m-d H:i'));
        fputs($L, $newline . "\n");
    }

    $newline = sprintf('%s: %s', date('Y-m-d H:i'), $fullarg);
    fputs($L, $newline . "\n");
    echo difftime() . ": $fullarg\n";
fclose($L);
