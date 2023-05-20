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

    $LastDayOfPrevMonth      = strtotime('yesterday', $firstDay);
    $FirstDayOfPrevMonth      = strtotime(date('Y-m-01', $LastDayOfPrevMonth));

    $twoMonthsBack = strtotime('-3 months');

    # keep going backward till before the prev month
    if ($FirstDayOfPrevMonth < $info['last_time']) {
        return find_start_of_last_month($L);
    }
    $info2 = [];

    # keep going forward till just after the first day of prev month
    while (true) {
        $info2 = iterate($L, true);
        if ($FirstDayOfPrevMonth < $info2['last_time'])
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
function find_start_of_month($L, $FirstDayOfMonth = 0)
{
    fseek($L, -2000, SEEK_CUR);
    //find first line
    $info          = iterate($L, true);

    if (!$FirstDayOfMonth) {
        die("Need first day of month");
    }

    # keep going backward till before the prev month
    if ($FirstDayOfMonth     < $info['last_time']) {
        return find_start_of_month($L, $FirstDayOfMonth);
    }
    $info2 = [];

    # keep going forward till just after the first day of prev month
    while (true) {
        $info2 = iterate($L, true);
        if ($FirstDayOfMonth < $info2['last_time'])
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
    if (intval($hh) > 0) {
        $mm = "{$hh}h" . $mm;
    }

    return $mm;
}

function undo($logfile)
{
    $lines = file($logfile);
    array_pop($lines);
    $filelines = join('', $lines);
    file_put_contents($logfile, $filelines);
}

/**
 * Collects some recent lines and parses them
 * @param  $L
 * @param  $only_first
 * @param  false         $callback
 * @global $last_comment saves last line
 * @return mixed
 */
function iterate($L, $only_first = false, $callback = null)
{
    global $last_time;
    global $last_comment;
    global $all_lines;
    global $lc;
    if (feof($L))
        return false;

    while (!feof($L)) {
        $line = trim(fgets($L));
        $lc++;
        if (!empty($line)) {
            //echo "$lc: $line\n";
            $ss = explode(' ', $line, 3);
            $ss2 = explode(':', $line, 3);
            if (count($ss) < 3) {
                continue;
            }

            list($last_dt, $comment) = $ss;

            $ss = explode(': ', $line, 3);
            if (count($ss) < 2) {
                continue;
            }

            if (strlen($ss[0]) != 16) {
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
            if (!empty($ss[2]))
                $all_lines[$last_time]['task'] = $ss[2];
            if ($only_first) {
                break;
            }

            if (is_callable($callback) && call_user_func($callback, $all_lines[$last_time])) {
                break;
            }
        }
    }
    return $all_lines[$last_time];
}

function getHourMins($times)
{
    $mins = round($times / 60);
    $hours = $mins / 60;
    $mins = $mins % 60;
    return sprintf("%d:%02d", $hours, $mins);
}


function push_logfile_to_git($logfile,$gitrepo,$pcname)
{
    $gitrepofile = $gitrepo . '/' . basename($logfile);
    #copy file $logfgile to $gitrepo directory
    //verify git repo is clean
    if(!verify_gitrepo_is_clean($gitrepo))
    {
        fprintf(STDERR, "Git repo is not clean\n");
        return false;
    }

    if(count_lines_in_file($logfile) < count_lines_in_file($gitrepofile))
    {
        $ctr1 = count_lines_in_file($logfile);
        $ctr2 = count_lines_in_file($gitrepofile);
        //fprintf(STDERR, __LINE__ . ":Line counts do not match\n");
        throw new Exception("Line counts do not match while preparing $ctr1 < $ctr2");
    }    
    
    //copy file
    copy($logfile, $gitrepofile);

    //git add
    exec("cd \"$gitrepo\" && git add " . basename($logfile));

    //git commit
    exec("cd \"$gitrepo\" && git commit -m 'auto commit $pcname'");

    //git push
    $rt = exec("cd \"$gitrepo\" && git push");
    if($rt)
    {
        fprintf(STDERR, "Git push failed\n");
        return false;
    }
}

/**
 * Do a git pull. Yeah, very helpful comment
 *
 * @return void
 */
function do_git_pull($gitrepo)
{
    $output = [];
    $ret = 0;
    chdir($gitrepo);
    exec("git pull", $output, $ret);
    if($ret != 0)
    {
        fprintf(STDERR, "Git pull failed\n");
        return false;
    }
    return true;
}

function count_lines_in_file($filepath)
{
    $ctr = count(file($filepath));
    echo "$filepath lines $ctr\n";
    return $ctr;
}
function copy_timelog_back_to_pc($gitrepofile,$logfile)
{
    //copy file
    if(!copy($gitrepofile, $logfile))
    {
        throw new Exception("Failed to copy file from $gitrepofile to $logfile");
    }
}

function check_if_git_behind($gitrepo)
{
    $output = [];
    $ret = 0;
    chdir($gitrepo);
    exec("git fetch", $output, $ret);
    if($ret != 0)
    {
        fprintf(STDERR, "Git fetch failed\n");
        return false;
    }
    exec("git status", $output, $ret);
    if($ret != 0)
    {
        fprintf(STDERR, "Git status failed\n");
        return false;
    }
    $output = join("\n", $output);
    if(strstr($output, 'Your branch is behind') !== false)
    {
        fprintf(STDERR, "Git branch is behind\n");
        return true;
    }
    return false;
}

/**
 * verify git repo is clean before doing any commits to prevent overwriting
 *
 * @param [type] $gitrepo
 * @return void
 */
function verify_gitrepo_is_clean($gitrepo)
{
    $output = [];
    $ret = 0;
    exec("cd \"$gitrepo\" && git status", $output, $ret);
    return $ret == 0;
}

function pull_updated_logfile($logfile,$gitrepo,$pcname)
{
    $gitrepofile = $gitrepo . '/' . basename($logfile);
    if(!verify_gitrepo_is_clean($gitrepo))
    {
        fprintf(STDERR, __LINE__ . ":Git repo is not clean\n");
        return false;
    }
    //if logfile line count less than git repo line count, means a big mess somewhere
    if(count_lines_in_file($logfile) != count_lines_in_file($gitrepofile))
    {
        $ctr1 = count_lines_in_file($logfile);
        $ctr2 = count_lines_in_file($gitrepofile);
        //fprintf(STDERR, __LINE__ . ":Line counts do not match\n");
        throw new Exception("Line counts do not match while preparing $ctr1 != $ctr2");
    }
    if(check_if_git_behind($gitrepo))
    {
        echo __LINE__ . ":Git repo is behind\n";
        if(!do_git_pull($gitrepo))
        {
            fprintf(STDERR, __LINE__ . ":Git pull failed\n");
            return false;
        }

        //if logfile line count same or more than git repo line count, means a big mess somewhere
        if(count_lines_in_file($logfile) >= count_lines_in_file($gitrepofile))
        {
            $ctr1 = count_lines_in_file($logfile);
            $ctr2 = count_lines_in_file($gitrepofile);
            fprintf(STDERR, __LINE__ . ":Did line counts decrease after git pull? local $ctr1 vs $ctr2 repo\n");
            return false;
        }

        copy_timelog_back_to_pc($gitrepofile,$logfile);

        echo "Logfile updated from git repo\n";
    }
    return true;
}
