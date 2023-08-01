<?php
namespace gtimelogphp;

class Project
{
    /**
     * @var mixed
     */
    public $task_times;

    /**
     * @var mixed
     */
    public $name;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param $info
     * @param $spent_time_secs
     */
    public function parse($info, $spent_time_secs)
    {
        $task = $info['task'] ?? 'default';
        if (empty($this->task_times[$task]))
        {
            $this->task_times[$task] = 0;
        }

        $this->task_times[$task] += $spent_time_secs;
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
        $rep['Total'] = $hours;
        return $rep;
    }
}
