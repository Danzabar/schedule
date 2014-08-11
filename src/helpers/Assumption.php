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
     *  Acitvity time
     *  used as an informative figure.
     * 
     */
    protected $activity_time;
    
    
    protected $schedule_name;
    
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
    
    
    public function __construct($name, $assume_time)
    {
        $this->schedule_name = $name;
        
        $this->assume_time = $assume_time;
        
        $this->dateStore = new DateStore($assume_time);       
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
        
        $total_hours = $this->dateStore->getTimeLeft();
        $time_counts = $this->dateStore->countStore();
        
        return array(
                $this->schedule_name => array (
                'schedule' => $this->dateStore->getStore(TRUE),
                'hours_left' => $total_hours['total'],
                'activity_time' => $time_counts['activity_time'],
                'activity_time_by_day' => $time_counts['activity_time_by_day'],
                'total_time' => $time_counts['total']
                ));
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
        foreach($activities as $label => $activity)
        {
            if(!empty($activity['times']))
            {
                foreach($activity['times'] as $day => $times)
                {
                    $this->dateStore->addTime($label, $day, $times);
                }
                
                $total_hours = $this->dateStore->getTimeLeft();
            }
            
            $total_activity_time =  $total_activity_time + $activity['max'];
            
            // Save this for further usage
            $this->activity_time[$label] = $activity['max'];
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
            if($activity['max'] > 0)
            {
                $this->addTimeToActivity($activity, $label);
            }
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
