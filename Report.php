<?php

namespace gtimelogphp;

class MonthReport
{
    /**
     * @var mixed
     */
    public $fHandle;
    /**
     * @var mixed
     */
    public $projects;
    /**
     * @param $logfile
     */
    public $last_time;
    public $last_info;

    /**
     * Holds cache or parsed report for a certain month
     *
     * @var array
     */
    public $reportData;

    /**
     * @param $logfile
     */
    public function __construct($logfile)
    {
        $this->init($logfile);
    }

    /**
     * @param $logfile
     */
    public function init($logfile)
    {
        $this->fHandle = fopen($logfile, 'r');
    }

    public function parse($FirstDayOfMonth)
    {
        fseek($this->fHandle, -200, SEEK_END);
        $info_prev_month = find_start_of_month($this->fHandle, $FirstDayOfMonth);
        #echo "Found: " . print_r($info_prev_month,true) . "\n";
        $current_month = '';
        $current_date = '';
        while (true)
        {
            $next = iterate($this->fHandle, true);
            if (!$next)
                break;
            if ($current_date != date('Y-m-d', $next['last_time']))
                $this->last_time = 0;
            if (empty($current_month))
                $current_month = date('Y-m', $next['last_time']);
            if ($current_month != date('Y-m', $next['last_time']))
                break;
            if (false == $next)
                break;

            if (substr($next['project'], -2) != '**')
            {
                if (empty($this->projects[$next['project']]))
                {
                    $this->projects[$next['project']] = new Project($next['project']);
                }

                $spent_time_secs = 0;
                if ($this->last_time)
                {
                    $spent_time_secs = $next['last_time'] - $this->last_time;
                }
                $next['spent_time_secs'] = $spent_time_secs;
                //print_r($next);
                $this->projects[$next['project']]->parse($next, $spent_time_secs, $this->last_time);
            }
            $this->last_time = $next['last_time'];
            $this->last_info = $next;
            $current_date = date('Y-m-d', $next['last_time']);
            continue;
        }
        //$info_this_month = find_start_of_this_month($L);
        /*$pos = -(strlen($info[1]) + strlen($info[2]) + 5);
    fseek($L, $pos, SEEK_CUR);
    iterate($L);*/
    }

    public function report($FirstDayOfMonth)
    {
        $this->parse($FirstDayOfMonth);
        $rep = [];
        foreach ($this->projects as $project_name => $project)
        {
            $rep[$project_name] = $project->report();
        }
        return $this->reportData = $rep;
    }

    /**
     * Will total all projects and show summary of hours spent in whole month 
     * along with productivity based on which are billable hours
     *
     * @return void
     */
    public function summary($FirstDayOfMonth)
    {
        if (empty($this->reportData))
            $this->report();
        $bill = new Bill($this->reportData);
        $total = 0;
        $billable = 0;
        $income = 0;
        $billable_projects = [];
        foreach ($this->reportData as $project_name => $project)
        {
            $total += $project['Total'];
            if (isset($bill->rates['projects'][$project_name]))
            {
                $stats = [];

                //calculate estimate for whole month based on current total
                $avgHoursPerDay = $project['Total'] / date('d');
                $stats['EstimatedTotalHours'] = round($avgHoursPerDay * 30);

                $billable += $project['Total'];
                $hour_inr_rate = $bill->rates['projects'][$project_name]['per_hour'];
                if ($bill->rates['projects'][$project_name]['ccy'] != 'INR')
                {
                    $hour_inr_rate = $bill->rates['projects'][$project_name]['per_hour'] * $bill->rates['ccy'][
                        $bill->rates['projects'][$project_name]['ccy']
                    ];
                    $stats['Income' . $bill->rates['projects'][$project_name]['ccy']] = $bill->rates['projects'][$project_name]['per_hour'] * $project['Total'];
                    $stats['EstimatedIncome' . $bill->rates['projects'][$project_name]['ccy']] = $bill->rates['projects'][$project_name]['per_hour'] * $stats['EstimatedTotalHours'];
                }   
                $stats['Total'] = $project['Total'];
                $stats['Dated'] = date('Y-m-d H:i', $project['Dated']);
                $stats['name'] = $project_name;
                $stats['Income'] = round($project['Total'] * $hour_inr_rate);
                $stats['EstimatedIncome'] = round($stats['EstimatedTotalHours'] * $hour_inr_rate);

                //sory array by keys
                ksort($stats);

                $income += $stats['Income'];
                $billable_projects[$project_name]['times'] = $project;
                $billable_projects[$project_name]['stats'] = $stats;
            }
            else
            {
                //echo "$project_name: " . round($project['Total']) . " hours is not billed\n";
            }
        }
        $rep['Total'] = round($total);
        $rep['Billable'] = round($billable);
        if ($rep['Billable'])
        {
            $rep['Income'] = round($income);
            $rep['Productivity'] = (round($billable / $total, 2) * 100) . "%";
            $rep['EarningDays'] = round(100 * ($rep['Billable'] / 8) / $this->getWOrkingDaysTillTOday($FirstDayOfMonth)) . "%";

            $rep['EffectiveHourlyRateINR'] = round($income / $billable);
            $rep['ThisMonthHourlyRateINR'] = round($income / (20 * 8));
            $this->saveStats($rep, $FirstDayOfMonth);
            $rep['BillableProjects'] = $billable_projects;
        }
        return $rep;
    }

    /**
     * Save stats by date in a json file
     *
     * @param [type] $rep
     * @return void
     */
    public function saveStats($rep, $FirstDayOfMonth)
    {
        $stats_file = $_ENV['TIMELOG_GITREPO'] . '/stats.json';
        $stats = json_decode(file_get_contents($stats_file), true);
        if (date('Y-m', $FirstDayOfMonth) == date('Y-m'))
            $stats[date('Y-m-d')] = $rep;
        $stats[date('Y-m', $FirstDayOfMonth)] = $rep;
        return file_put_contents($stats_file, json_encode($stats, JSON_PRETTY_PRINT));
    }

    public function getWOrkingDaysTillTOday($FirstDayOfMonth)
    {
        $firstDay = $FirstDayOfMonth;
        $mon = date('m', $FirstDayOfMonth);
        echo "mon:$mon $FirstDayOfMonth=$FirstDayOfMonth, " . date('d-m-Y', $FirstDayOfMonth) . "\n";
        $today = strtotime(date('Y-m-d'));
        $workingDays = 0;
        while ($firstDay < $today)
        {
            $firstDay = strtotime('+1 day', $firstDay);
            if ($mon != date('m', $firstDay))
                break;
            if (date('N', $firstDay) < 6)
                $workingDays++;
        }
        return $workingDays;
    }

    public function makeGraph($summary)
    {
        //use termgraph command to make graphs
        $termgraph = getenv('HOME') . '/.local/bin/termgraph';
        $file1dat = __DIR__ . '/file1.dat';
        $F1 = fopen($file1dat, 'w');
        fwrite($F1, "Total," . $summary['Total'] . "\n");
        fwrite($F1, "Billable," . $summary['Billable'] . "\n");
        fclose($F1);
        $cmd = "$termgraph $file1dat --title \"Month Report\"";
        system($cmd);  
        
        $file2dat = __DIR__ . '/file2.dat';
        $F2 = fopen($file2dat, 'w');
        foreach ($summary['BillableProjects'] as $project_name => $project)
        {
            fwrite($F2, $project_name . "," . round($project['stats']['Total']) . "\n");
        }
        fclose($F2);
        $cmd = "$termgraph $file2dat --title \"Billable Projects\"";
        system($cmd);

    }
}
