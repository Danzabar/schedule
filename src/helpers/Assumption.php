<?php namespace Danzabar\Schedule\Helpers;

use Danzabar\Schedule\Helpers\DateStore; 

Class Assumption
{
    /**
     *  An array containing the ASSUMED settings from the details
     *  passed by Schedule class.
     * 
     */
    protected $settings;
    
    /**
     *  Mapped key words such as Weekday and Everyday to numerical
     *  day equivilents. 
     * 
     */
    protected $day_maps;
    
    /**
     *  Assume missing time or remove time that is over.
     * 
     */
    protected $assume_time;
    
    /**
     *  The formatted array
     * 
     */
    public $format;
    
    
    /**
     *  The time left for each day - Gets reset when time is taken away
     *  for activities;
     * 
     */
    public $time_left;
    
    
    public function __construct($assume_time)
    {
        $this->settings = array();
        
        $this->assume_time = $assume_time;
        
        $this->dateStore = new DateStore;       
    }

    /** 
     *  Builds the settings variable based on the settings it receives from
     *  the Schedule class.
     * 
     */
    public function process($excludes, $activities)
    {
        // First send over the excluded times
        foreach($excludes as $label => $dates)
        {
            // Add the times
            foreach($dates as $day => $times)
            {
                $this->dateStore->addTime($label, $day, $times);
            }
        }
        
        // Process the activities
        $this->processActivities($activities);
       

        return $this->dateStore->getStore();
    }
    
    /**
     *  Process Activities - uses the date store to allocate time to activities,
     *  and then adds that time to the date store.
     * 
     */
    public function processActivities($activities)
    {
        // Get the total hours left;
        $total_hours = $this->dateStore->getTimeLeft();
        $total_activity_time = 0;
        
        // total activity times
        foreach($activities as $activity)
        {
            $total_activity_time =  $total_activity_time + $activity['max'];
        }
        
        if($this->assume_time)
        {
            if($total_hours['total'] > $total_activity_time)
            {
                // We should allocate some more time
                
            } else
            {
                // We should remove some time
                
            }        
        }
        
        // Keep a local copy of the timeleft after exclusion so we can manipulate this
        $this->time_left = $this->dateStore->getTimeLeft(FALSE);
        
        // At this point we have our quota hours. Lets fill our activities with times.
        foreach($activities as $label =>  $activity)
        {
            $activity['times'] = $this->addTimeToActivity($activity, $label);
        }
    }
    
    /**
     *  Inserts time values for single activities. 
     *  - Some rules about this:
     *      - We prefer to do 2 hour + blocks, where possible
     *      - The selection of time should be random, so each run will produce different results
     * 
     */
    private function addTimeToActivity($activity, $label)
    {
        $day_allocation = $this->dateStore->splitByDays($activity['max'], $this->time_left);      
        
        // Now we have allocated the hours correctly to days, we need to actually
        // give them proper start and end times, eg 10:00 - 15:00
        // The dateStores ->getHoursInDay will do this.
        foreach($day_allocation as $day => $hours)
        {
            if($hours > 0)
            {
                $times = $this->dateStore->hoursToTime($day, $hours);
                
                $this->dateStore->addTime($label, array($day), $times);
            }            
        }
    }
    
    
    
}
