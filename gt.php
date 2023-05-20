#!/usr/bin/env php
<?php
namespace gtimelogphp;

ini_set('xdebug.log_level',0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__ . "/vendor/autoload.php";
require_once 'functions.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pcname = $_ENV['TIMELOG_PCNAME'] ?? "MyPc";
$gitrepo = $_ENV['TIMELOG_GITREPO'] ?? "";
//getenv('HOME') . '/.local/share/gtimelog/timelog.txt';
$logfile = $_ENV['TIMELOG_FILEPATH'] ?? "";

if(empty($logfile))
{
    echo "Please set TIMELOG_FILEPATH in .env file\n";
    exit(1);
}
if(empty($gitrepo))
{
    echo "Please set TIMELOG_GITREPO in .env file\n";
    exit(1);
}

if(empty($pcname))
{
    echo "Please set TIMELOG_PCNAME in .env file\n";
    exit(1);
}


$hello_cmd = new \Commando\Command();

$hello_cmd->option()
    ->describedAs('Log entry');

$hello_cmd->option('s')
    ->aka('sync')
    ->describedAs('Just pull from git')
    ->boolean();

$hello_cmd->option('b')
    ->aka('bill')
    ->describedAs('Generate monthly bill report')
    ->boolean();

$hello_cmd->option('e')
    ->aka('earning')
    ->describedAs('Just report this months earnings')
    ->boolean();

$hello_cmd->option('i')
    ->aka('inum')
    ->describedAs('Get next invoice num')
    ->boolean();

    // Define a boolean flag "-c" aka "--capitalize"
$hello_cmd->option('m')
    ->aka('month')
    ->describedAs('Report for Month (last_month, this_month or YYYY-MM0)')
    ->default("last_month");

$hello_cmd->option('p')
    ->aka('project')
    ->describedAs('Generate monthly bill json');

$hello_cmd->option('r')
    ->aka('report')
    ->describedAs('Report on last months projects and the times')
    ->boolean();

$hello_cmd->option('u')
    ->aka('undo')
    ->describedAs('Undo the last log')
    ->boolean();

date_default_timezone_set('Asia/Kolkata');
$all_lines = [];

if (!file_exists($logfile))
{
    fputs(STDERR, "$logfile: File not found\n");
    return -1;
}
$away = false;
$argv2 = $argv;

if ($hello_cmd['inum'])
{
    $num = Bill::getNextInvoiceNumber();
    echo "Next invoice num:" . $num;
    return 0;
}

if ($hello_cmd['sync'])
{
    do_git_pull($gitrepo);
    return 0;
}

if ($hello_cmd['report'] || $hello_cmd['bill'] || $hello_cmd['earning'])
{
    $rep = new MonthReport($logfile);
    if ('last_month' == $hello_cmd['month'])
    {
        $FirstDayOfMonth = strtotime(date('Y-m-01', strtotime("-1 month")));
    }
    else if ('this_month' == $hello_cmd['month'])
    {
        $FirstDayOfMonth = strtotime(date('Y-m-01'));
    }
    else
    {
        $FirstDayOfMonth = strtotime(date('Y-m-01', strtotime($hello_cmd['month'])));
    }

    #echo "FirstDayOfMonth = " . date('Y-m-d',$FirstDayOfMonth) . "\n";
    $report_data = $rep->report($FirstDayOfMonth);
    if ($hello_cmd['report'])
    {
        print_r($report_data);
        print_r($rep->summary());
    }
    else if ($hello_cmd['bill'] || $hello_cmd['earning'])
    {
        $bill = new Bill($report_data);
        $rep = $bill->report();
        if ($hello_cmd['earning'])
        {
            echo sprintf("%d", round($rep['TotalEarning'])) . "\n";
        }
        else if ($hello_cmd['project'])
        {
            $proj = $hello_cmd['project'];
            $bill->saveJson($rep[$proj], $proj);
            print_r($rep[$proj]);
        }
        else
        {
            print_r($rep);
        }

    }
    return;
}

if ($hello_cmd['undo'])
{
    undo($logfile);
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
        $away = true;
        $fullarg = $last_comment;
    }
}

//if not arg given, we just show time spent doing the last item
if (empty($argv[1]))
{
    $mm = difftime();
    echo "$mm: $last_comment\n";
    return -1;
}

if(!pull_updated_logfile($logfile,$gitrepo,$pcname))
{
    fprintf(STDERR, "Failed to pull updated logfile\n");
    return -1;
}

#Writing
$L = fopen($logfile, 'a');
#fseek($L, -200, SEEK_END);

$last_dt = date('Y-m-d', $last_time);
$today_date = date('Y-m-d');
//Not same day
if (strstr($last_dt, $today_date) === false)
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
push_logfile_to_git($logfile,$gitrepo,$pcname);
