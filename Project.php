<?php

namespace gtimelogphp;

class Project
{

    /**
     * 
     * @var mixed
     */
    public $last_datetime;

    /**
     * @var mixed
     */
    public $task_times;

    public $dates;

    public $task_week_times;

    /**
     * @var mixed
     */
    public $name;

    public $billingInfo;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $json = \file_get_contents($_ENV['RATES_JSON_FILE']);
        $rates = json_decode($json, true);
        $this->billingInfo = $rates['projects'][$name] ?? [];
    }

    function getDateTimeFromWeekNumber($weekNumber, $year)
    {
        // Create a new DateTime object for the first day of the year
        $dateTime = new \DateTime();
        $dateTime->setISODate($year, $weekNumber, 1); // Set to the first day of the specified week

        // Calculate the number of days to add to get to the desired week
        //$daysToAdd = ($weekNumber - 1) * 7;
        //$dateTime->add(new \DateInterval("P{$daysToAdd}D"));
        //convert to string
        return $dateTime->format('Y-m-d');
    }

    /**
     * @param $info
     * @param $spent_time_secs
     */
    public function parse($info, $spent_time_secs, $tasktime)
    {
        $task = $info['task'] ?? 'default';
        if (empty($this->task_times[$task]))
        {
            $this->task_times[$task] = 0;
        }
        $week = date('W', $ts = strtotime($info['given_ts']));
        $year = date('Y', $ts);
        $dated = date('Y-m-d', $ts);

        $weekDateTime = $this->getDateTimeFromWeekNumber($week, $year);

        if (empty($this->task_week_times[$weekDateTime][$task]))
        {
            $this->task_week_times[$weekDateTime][$task] = 0;
        }
        $this->task_week_times[$weekDateTime][$task] += $spent_time_secs;
        $this->task_times[$task] += $spent_time_secs;
        $this->last_datetime = $tasktime;
        if (empty($this->dates[$dated]))
        {
            $this->dates[$dated] = 0;
        }
        $this->dates[$dated] += $spent_time_secs;
    }

    public function report()
    {
        $rep = [];
        $tasks = [];
        $times = $total = 0;

        foreach ($this->task_times as $task => $times)
        {
            $tasks[$task] = getHourMins($times);
            $total += $times;
        }
        list($hours, $mins) = explode(':', getHourMins($total));
        $hours += $mins / 60;
        $rep['billingactive'] = !empty($this->billingInfo);
        $rep['Weeks'] = $this->task_week_times;

        $dates = $this->dates;
        //remove array elements with zero
        foreach ($dates as $date => $time)
        {
            if ($time == 0)
                unset($dates[$date]);
        }
        $rep['Tasks'] = $tasks;
        $rep['Dates'] = $dates;
        $rep['Total'] = $hours;
        $rep['Dated'] = $this->last_datetime;
        return $rep;
    }

    public function logNow($fullarg,$logfile,$argv,$gitrepo,$pcname)
    {
        global $last_comment;
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

        if (!pull_updated_logfile($logfile, $gitrepo, $pcname))
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
        push_logfile_to_git($logfile, $gitrepo, $pcname);
    }
}
