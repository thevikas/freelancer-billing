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

    function getDateTimeFromWeekNumber($weekNumber, $year) {
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
    public function parse($info, $spent_time_secs,$tasktime)
    {
        $task = $info['task'] ?? 'default';
        if (empty($this->task_times[$task]))
        {
            $this->task_times[$task] = 0;
        }
        $week = date('W',$ts = strtotime($info['given_ts']));
        $year = date('Y',$ts = strtotime($info['given_ts']));
        $weekDateTime = $this->getDateTimeFromWeekNumber($week, $year);

        if(empty($this->task_week_times[$weekDateTime][$task]))
        {
        	$this->task_week_times[$weekDateTime][$task] = 0;
        }
        $this->task_week_times[$weekDateTime][$task] += $spent_time_secs;
        $this->task_times[$task] += $spent_time_secs;
        $this->last_datetime = $tasktime;
    }

    public function report()
    {
        $rep = [];
        $times = $total = 0;

        foreach($this->task_times as $task => $times)
        {
            $rep[$task] = getHourMins($times);
            $total += $times;
        }
        list($hours,$mins) = explode(':',getHourMins($total));
        $hours += $mins/60;
        $rep['billingactive'] = !empty($this->billingInfo);
        $rep['Weeks'] = $this->task_week_times;
        $rep['Total'] = $hours;
        $rep['Dated'] = $this->last_datetime;
        return $rep;
    }
}
