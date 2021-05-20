<?php
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
    $firstDay      = strtotime(date('Y-m-01'));
    $twoMonthsBack = strtotime('-3 months');
    if ($firstDay < $info[0])
    {
        return find_start_of_last_month($L);
    }

    return $info;
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

function makeReport()
{
    global $all_lines, $logfile;

    $L = fopen($logfile, 'r');
    fseek($L, -200, SEEK_END);
    $info = find_start_of_last_month($L);
    $info = find_start_of_this_month($L);
    fseek($L, -(strlen($info[1] + $info[2] + 5)), SEEK_CUR);
    iterate($L);

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
    while (!feof($L))
    {
        $line = trim(fgets($L));
        $lc++;
        if (!empty($line))
        {
            //echo "$lc: $line\n";
            $ss = explode(' ', $line, 3);
            if (count($ss) < 3)
            {
                continue;
            }

            list($last_dt, $comment) = $ss;

            $ss = explode(': ', $line, 2);
            if (count($ss) < 2)
            {
                continue;
            }

            if (strlen($ss[0]) != 16)
            {
                continue;
            }

            $last_time             = strtotime($ss[0]);
            $last_comment          = $ss[1];
            $all_lines[$last_time] = [$last_time, $ss[0], $last_comment];
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
