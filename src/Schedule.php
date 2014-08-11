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
     *  An instance of the Builder Class
     * 
     */
    protected $builder;
    
    /**
     *  An instance of the Assumption class
     * 
     */
    protected $assumption;
    
    
    public function __construct($name, $assume_missing_time = FALSE)
    {
        $this->name = $name;
        
        // Set vars as empty arrays where nessecary
        $this->excluded_times = array();
        $this->activities = array();        
        
        $this->assumption = new \Danzabar\Schedule\Helpers\Assumption($this->name, $assume_missing_time);       
    }
    
    /**
     *  Takes the current settings and passes them to the builder, who
     *  will return a json formatted schedule for us.
     * 
     */
    public function build()
    {
        $settings = $this->assume();
        
        return new \Danzabar\Schedule\Helpers\Builder($settings, $this->name);
    }
    
    /**
     *  Allows you to update excluded times.
     * 
     */
    public function setExcludes($excludes = array(), $label)
    {
        $this->excluded_times[$label] = $excludes;
        
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
    
    /**
     *  Uses the assumption class to build a full day of allocated time,
     *  including extending the hours from already made allocations
     *  and assigning hours and times to day from activities we are yet
     *  to specify time for.
     * 
     */
    private function assume()
    {
        return $this->assumption->process($this->excluded_times, $this->activities);      
    }
}
