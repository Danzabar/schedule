<?php namespace Danzabar\Schedule\Helpers;

Class Builder
{
    /**
     *  The raw data that this class recieves.
     * 
     */
    protected $raw;
    
    /**
     *  The name of the schedule
     * 
     */
    protected $name;
    
    /**
     *  The actual time map array.
     * 
     */
    protected $schedule;
    
    /**
     *  Activity time, the time spent on each activity in total.
     *  
     */
    protected $activity_time;
    
    /**
     *  The time spent on activities by day
     * 
     */
    protected $activity_time_by_day;
    
    /**
     *  The total time allocated to schedule
     * 
     */
    protected $total_time;    
    
    /**
     *  Time left, unused time left in hours. 
     * 
     */
    protected $time_left;
    
    
    protected $day_map = array(
        0 => 'sunday',
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday'
    );
    
    public function __construct($data, $name)
    {
        // Save the raw data
        $this->raw = $data;
        
        // Using the name, extract all the parts we need.
        $this->name = $name;   
        
        $this->schedule = $this->raw[$this->name]['schedule'];
        $this->activity_time = $this->raw[$this->name]['activity_time'];
        $this->time_left = $this->raw[$this->name]['hours_left'];
        $this->activity_time_by_day = $this->raw[$this->name]['activity_time_by_day'];
        $this->total_time = $this->raw[$this->name]['total_time'];
    }
    
    /**
     *  Converts the indexs to day names, 0 = sunday etc.
     * 
     */
    public function convertIndexes()
    {
        $replace = array();
        
        foreach($this->schedule as $day => $times)
        {
            $replace[$this->day_map[$day]] = $times; 
        }
        
        $this->schedule = $replace;
        
        return $this;
    }
    
    /**
     *  Returns the activity time by day
     *  
     */
    public function getActivityTimeByDay()
    {
        return $this->activity_time_by_day;
    }
    
    /**
     *  Returns the total time
     * 
     */
    public function getTotalTime()
    {
        return $this->total_time;
    }
    
    /**
     *  Returns the name
     * 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     *  returns the time left
     * 
     */
    public function getTimeLeft()
    {
        return $this->time_left;
    }
    
    /**
     *  Returns the activity time;
     *
     */
    public function getActivityTime()
    {
        return $this->activity_time;
    }
    
    /**
     *  Returns the schedule in JSON format.
     * 
     */
    public function toJSON()
    {
        return json_encode($this->schedule, JSON_PRETTY_PRINT);
    }
}
