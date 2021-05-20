#!/usr/bin/env php
<?php
require_once('functions.php');
date_default_timezone_set('Asia/Kolkata');
$all_lines = [];

$logfile = getenv('HOME') . '/.gtimelog/timelog.txt';
if (!file_exists($logfile))
{
    fputs(STDERR, "$logfile: File not found\n");
    return -1;
}
$away  = false;
$argv2 = $argv;
unset($argv2[0]);
foreach ($argv2 as $arg)
{
    if ('-report' == $arg)
    {
        return makeReport();
    }
}
$fullarg = implode(' ', $argv2);
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
fseek($L, -200, SEEK_END);

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
