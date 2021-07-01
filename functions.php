<?php
require_once('Report.php');
require_once('Project.php');
require_once('Bill.php');
$verbose = true;
/**
 * @param  $info
 * @return mixed
 */
function checkIfItsThisMonth($info)
{
    $firstDay            = strtotime(date('Y-m-01'));
    list($tt, $tt2, $cc) = $info;
    return $firstDay < $tt;
}

/**
 * @param  $L
 * @return mixed
 */
function find_start_of_last_month($L)
{
    fseek($L, -200, SEEK_CUR);
    //find first line
    $info          = iterate($L, true);

    # FIrst day of this month
    $firstDay      = strtotime(date('Y-m-01'));

    $LastDayOfPrevMonth      = strtotime('yesterday',$firstDay);
    $FirstDayOfPrevMonth      = strtotime(date('Y-m-01',$LastDayOfPrevMonth));

    $twoMonthsBack = strtotime('-3 months');

    # keep going backward till before the prev month
    if ($FirstDayOfPrevMonth < $info['last_time'])
    {
        return find_start_of_last_month($L);
    }
    $info2 = [];

    # keep going forward till just after the first day of prev month
    while(true) {
        $info2 = iterate($L, true);
        if($FirstDayOfPrevMonth < $info2['last_time'])
            break;
    }
    return $info2;
    //check month
    //if
}

/**
 * Finds start of prev month by default
 *
 * @param  $L
 * @param  $FirstDayOfMonth
 * @return mixed
 */
function find_start_of_month($L,$FirstDayOfMonth = 0)
{
    fseek($L, -2000, SEEK_CUR);
    //find first line
    $info          = iterate($L, true);

    if(!$FirstDayOfMonth)
    {
        die("Need first day of month");
    }

    # keep going backward till before the prev month
    if ($FirstDayOfMonth     < $info['last_time'])
    {
        return find_start_of_month($L,$FirstDayOfMonth);
    }
    $info2 = [];

    # keep going forward till just after the first day of prev month
    while(true) {
        $info2 = iterate($L, true);
        if($FirstDayOfMonth < $info2['last_time'])
            break;
    }
    return $info2;
    //check month
    //if
}

/**
 * @param  $L
 * @return mixed
 */
function find_start_of_this_month($L)
{
    $info = iterate($L, false, 'checkIfItsThisMonth');
    return $info;
    //check month
    //if
}

function difftime()
{
    global $last_time;
    $diff = time() - $last_time;
    $hh   = gmdate('H', $diff);
    $mm   = gmdate('i', $diff) . 'm';
    if (intval($hh) > 0)
    {
        $mm = "{$hh}h" . $mm;
    }

    return $mm;
}

/**
 * @param  $L
 * @param  $only_first
 * @param  false         $callback
 * @return mixed
 */
function iterate($L, $only_first = false, $callback = null)
{
    global $last_time;
    global $last_comment;
    global $all_lines;
    global $lc;
    if(feof($L))
        return false;

    while (!feof($L))
    {
        $line = trim(fgets($L));
        $lc++;
        if (!empty($line))
        {
            //echo "$lc: $line\n";
            $ss = explode(' ', $line, 3);
            $ss2 = explode(':', $line, 3);
            if (count($ss) < 3)
            {
                continue;
            }

            list($last_dt, $comment) = $ss;

            $ss = explode(': ', $line, 3);
            if (count($ss) < 2)
            {
                continue;
            }

            if (strlen($ss[0]) != 16)
            {
                continue;
            }

            $last_time             = strtotime($ss[0]);
            $last_comment          = trim($ss2[2]);
            $all_lines[$last_time] = [
                'last_time' => $last_time,
                'given_ts' => $ss[0],
                'project' => $ss[1],
                'this_line_len' => strlen($line)
            ];
            if(!empty($ss[2]))
                $all_lines[$last_time]['task'] = $ss[2];
            if ($only_first)
            {
                break;
            }

            if (is_callable($callback) && call_user_func($callback, $all_lines[$last_time]))
            {
                break;
            }

        }
    }
    return $all_lines[$last_time];
}

function getHourMins($times)
{
    $mins = round($times/60);
    $hours = $mins / 60;
    $mins = $mins % 60;
    return sprintf("%d:%02d",$hours,$mins);

}