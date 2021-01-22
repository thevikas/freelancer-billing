#!/usr/bin/env php
<?php
date_default_timezone_set('Asia/Kolkata');
$all_lines = [];

$logfile = getenv('HOME') . "/.gtimelog/timelog.txt";
if (!file_exists($logfile)) {
    fputs(STDERR,"$logfile: File not found\n");
    return -1;
}
$away=false;
$argv2 = $argv;
unset($argv2[0]);
foreach($argv2 as $arg)
{
    if('-report' == $arg)
    {
        return makeReport();
    }
}
$fullarg = implode(" ",$argv2);
#Reading
$L = fopen($logfile,"r");
fseek($L,-200,SEEK_END);
$last_dt = $last_time = $lc = 0;
iterate($L);
function iterate($L,$only_first = false,$callback = null)
{
    global $last_time;
    global $last_comment;
    global $all_lines;
    global $lc;
    while(!feof($L))
    {
        $line = trim(fgets($L));
        $lc++;
        if(!empty($line))
        {
            //echo "$lc: $line\n";
            $ss = explode(' ',$line,3);
            if(count($ss)<3)
                continue;
            list($last_dt,$comment) = $ss;

            $ss = explode(': ',$line,2);
            if(count($ss)<2)
                continue;
            if(strlen($ss[0]) != 16)
                continue;
            $last_time = strtotime($ss[0]);
            $last_comment = $ss[1];
            $all_lines[$last_time] = [$last_time,$ss[0],$last_comment];
            if($only_first)
                break;
            if(is_callable($callback) && call_user_func($callback,$all_lines[$last_time]))
                break;
        }
    }
    return $all_lines[$last_time];
}
fclose($L);

if(!empty($argv[1]))
{
    if("last" == $argv[1])
        $fullarg = $last_comment;
    else if("away" == $argv[1])
    {
        $away = true;
        $fullarg = $last_comment;
    }
}

function difftime()
{
    global $last_time;
    $diff = time() - $last_time;
    $hh = gmdate("H",$diff);
    $mm = gmdate("i",$diff) . "m";
    if(intval($hh)>0)
        $mm = "{$hh}h" . $mm;
    return $mm;
}

if(empty($argv[1])) //if not arg given, we just show time spent doing the last item
{
    $mm = difftime();
    echo "$mm: $last_comment\n";
    return -1;
}

#Writing
$L = fopen($logfile,"a");
fseek($L,-200,SEEK_END);

$today_date = date('Y-m-d');
if(strstr($last_dt,$today_date) === false) //Not same day
    fputs($L,"\n");

//mark a time period as away between last and this and resume the work
if($away)
{
    $newline = sprintf("%s: away **",date('Y-m-d H:i'));
    fputs($L,$newline . "\n");
}

$newline = sprintf("%s: %s",date('Y-m-d H:i'),$fullarg);
fputs($L,$newline . "\n");
echo difftime() . ": $fullarg\n";
fclose($L);

function checkIfItsThisMonth($info)
{
    $firstDay = strtotime(date('Y-m-01'));
    list($tt,$tt2,$cc) = $info;
    return $firstDay < $tt;
}

function find_start_of_last_month($L)
{
    fseek($L,-200,SEEK_CUR);
    //find first line
    $info = iterate($L,true);
    $firstDay = strtotime(date('Y-m-01'));
    $twoMonthsBack = strtotime("-3 months");
    if($firstDay < $info[0])
        return find_start_of_last_month($L);
    return $info;
    //check month
    //if
}

function find_start_of_this_month($L)
{
    $info = iterate($L,false,"checkIfItsThisMonth");
    return $info;
    //check month
    //if
}

function makeReport()
{
    global $all_lines,$logfile;

    $L = fopen($logfile,"r");
    fseek($L,-200,SEEK_END);
    $info = find_start_of_last_month($L);
    $info = find_start_of_this_month($L);
    fseek($L,-(strlen($info[1] + $info[2] + 5)),SEEK_CUR);
    iterate($L);

}