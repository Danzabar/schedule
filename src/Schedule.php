<?php namespace Danzabar\Schedule;

/**
 *  What this file does/should do.
 *  
 *  1 - take in information regarding schedule.
 *  2 - liase with various helper classes to build and produce schedule in JSON format.
 *  
 */
Class Schedule
{   
    /**
     * The current/new Schedule name. 
     * (String)
     */
    protected $name;
    
    /**
     *  The times you wish to exclude from the schedule.
     *  ie - ["monday" => ['17:00' => '18:00', '18:00' => '18:30']]
     *  
     *  (array)
     */
    protected $excluded_times;
    
    /**
     *  The activities and amount of hours you wish to spend on them,
     *  if the hours are not included, it will be done by the script.
     *  
     *  (array)
     */
    protected $activities;
    
    /**
     *  Work/Wasted hours
     *  the hours you devote to the rat race. Same format as excluded times 
     *  (array)
     */
    protected $work_hours;
    
    /**
     *  An instance of the Builder Class
     * 
     */
    protected $builder;
    
    /**
     *  An instance of the Assumption class
     * 
     */
    protected $assumption;
    
    
    public function __construct($name)
    {
        $this->name = $name;
        
        // Set vars as empty arrays where nessecary
        $this->excluded_times = array();
        $this->activities = array();
        $this->work_hours = array();
        
        $this->builder = new \Danzabar\Schedule\Helpers\Builder;
        $this->assumption = new \Danzabar\Schedule\Helpers\Assumption;       
    }
    
    /**
     *  Allows you to update excluded times.
     * 
     */
    public function setExcludes($excludes = array())
    {
        $this->excluded_times = $excludes;
        
        return $this;
    }
    
    public function setWorkHours($hours = array())
    {
        $this->work_hours = $hours;
        
        return $this;
    }
    
    public function addActivity($name, $max = NULL, $times = array())
    {
        $this->activities[$name] = array('max' => $max, 'times' => $times); 
        
        return $this;
    }
}
