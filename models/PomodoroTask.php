<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 * 
 * @property string $project
 * @property string $task
 * @property int $last_time
 * @property int $spent_time_secs
 * 
 */
class PomodoroTask extends Model
{    
    var $status;
    var $last_time;
    var $project;
    var $task;
    var $duration;

    public function rules()
    {
        return [
            [['project', 'task','status'], 'required'],
            [['last_time'], 'integer'],
        ];
    }    
    
    public function getSpent_time_secs()
    {
        return time() - $this->last_time;
    }
}