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
     *  The step up of time, currently only supports hourly, but plans
     *  for the future at early stages are better
     * 
     */
    protected $increments;
    
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
        
        $this->increments = 'hourly';
        
        $this->builder = new \Danzabar\Schedule\Helpers\Builder;
        $this->assumption = new \Danzabar\Schedule\Helpers\Assumption;       
    }
    
    /**
     *  Takes the current settings and passes them to the builder, who
     *  will return a json formatted schedule for us.
     * 
     */
    public function build()
    {
        $settings = $this->assume();
        
        print_r($settings);
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
    
    /**
     *  Update the work hours variable.
     *  
     */
    public function setWorkHours($hours = array())
    {
        $this->work_hours = $hours;
        
        return $this;
    }
    
    /**
     *  The add activity function accepts a SINGLE activity. The reasoning
     *  behind this is to reduce the complexity needed to add it, its alot
     *  easier calling this multiple times than it is to format a massive
     *  array with the details this requires.
     * 
     * 
     */
    public function addActivity($name, $max = NULL, $times = array())
    {
        $this->activities[$name] = array('max' => $max, 'times' => $times); 
        
        return $this;
    }
    
    private function assume()
    {
        return $this->assumption->process($this->excluded_times, $this->work_hours, $this->activities, $this->increments);      
    }
}
