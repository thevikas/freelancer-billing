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
        $info_prev_month = find_start_of_month($this->fHandle,$FirstDayOfMonth);
        #echo "Found: " . print_r($info_prev_month,true) . "\n";
        $current_month = '';
        $current_date = '';
        while (true)
        {
            $next = iterate($this->fHandle, true);
            if(!$next)
                break;
            if($current_date != date('Y-m-d',$next['last_time']))
                $this->last_time = 0;
            if(empty($current_month))
                $current_month = date('Y-m',$next['last_time']);
            if($current_month != date('Y-m',$next['last_time']))
                break;
            if(false == $next)
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
                $this->projects[$next['project']]->parse($next, $spent_time_secs);
            }
            $this->last_time = $next['last_time'];
            $this->last_info = $next;
            $current_date = date('Y-m-d',$next['last_time']);
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
        foreach($this->projects as $project_name => $project)
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
        if(empty($this->reportData))
            $this->report();
        $bill = new Bill($this->reportData);
        $total = 0;
        $billable = 0;
        $income = 0;
        $billable_projects = [];
        foreach($this->reportData as $project_name => $project)
        {
            $total += $project['Total'];
            if(isset($bill->rates['projects'][$project_name]))
            {
                $billable += $project['Total'];
                $hour_inr_rate = $bill->rates['projects'][$project_name]['per_hour'];
                if($bill->rates['projects'][$project_name]['ccy'] != 'INR')
                {
                    $hour_inr_rate = $bill->rates['projects'][$project_name]['per_hour'] * $bill->rates['ccy'][
                        $bill->rates['projects'][$project_name]['ccy']
                    ];
                }   
                $project['name'] = $project_name;
                $project['Total'] * $hour_inr_rate;
                $project['Income'] = round($project['Total'] * $hour_inr_rate);
                $income += $project['Income'];
                $billable_projects[$project_name] = $project;

            }
            else
            {
                echo "$project_name: " . round($project['Total']) . " hours is not billed\n";
            }
        }
        $rep['BillableProjects'] = $billable_projects;
        $rep['Total'] = round($total);
        $rep['Billable'] = round($billable);
        $rep['Income'] = round($income);
        $rep['Productivity'] = (round($billable/$total,2) * 100) . "%";
        $rep['EarningDays'] = round(100*($rep['Billable']/8)/$this->getWOrkingDaysTillTOday($FirstDayOfMonth)) . "%";
        $rep['EffectiveHourlyRateINR'] = round($income/$billable);
        return $rep;
    }

    public function getWOrkingDaysTillTOday($FirstDayOfMonth)
    {
        $firstDay = $FirstDayOfMonth;
        $mon = date('m',$FirstDayOfMonth);
        echo "mon:$mon $FirstDayOfMonth=$FirstDayOfMonth, " . date('d-m-Y',$FirstDayOfMonth) . "\n";
        $today = strtotime(date('Y-m-d'));
        $workingDays = 0;
        while($firstDay < $today)
        {
            $firstDay = strtotime('+1 day',$firstDay);
            if($mon != date('m',$firstDay))
                break;
            if(date('N',$firstDay) < 6)
                $workingDays++;
        }
        return $workingDays;
    }
}
