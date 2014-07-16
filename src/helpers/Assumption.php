<?php namespace Danzabar\Schedule\Helpers;

use Carbon\Carbon;

Class Assumption
{
    /**
     *  Class constants that help us with our calculations and
     *  can easily be changed for testing or variation of results.
     * 
     */    
    const WEEK_START = 0; // Sunday
    const DATE_REGION = 'Europe/London';
    
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
    
    
    public function __construct()
    {
        $this->settings = array();
        
        date_default_timezone_set(self::DATE_REGION);
        
        $this->day_maps = array(
            'Weekday'   => array(1,2,3,4,5),
            'Everyday'  => array(0,1,2,3,4,5,6),
            'Weekend'   => array(0,6),
            'Sunday'    => array(0),
            'Monday'    => array(1),
            'Tuesday'   => array(2),
            'Wednesday' => array(3),
            'Thursday'  => array(4),
            'Friday'    => array(5),
            'Saturday'  => array(6)
        );
    }
    
    /** 
     *  Builds the settings variable based on the settings it receives from
     *  the Schedule class.
     * 
     */
    public function process($excludes, $work_hours, $activities, $increments)
    {
        $this->formatArr($excludes, $work_hours, $activities);
        
        print_r($this->format);
    }
    
    /**
     *  Format Arr function formats the excludes, work hours and activities
     *  into a readable array.
     * 
     */
    private function formatArr($excludes, $work_hours, $activities)
    {
        $this->format = array(
            0 => array(), // Sunday
            1 => array(), // Monday
            2 => array(), // Tuesday
            3 => array(), // Wednesday
            4 => array(), // Thursday
            5 => array(), // Friday
            6 => array(), // Saturday
        );
        
        // Add the excludes
        if(!empty($excludes))
        {
            $this->extractTimes($excludes, 'Excluded');
        }
        
        if(!empty($work_hours))
        {
            $this->extractTimes($work_hours, 'Work');
        }
        
        $this->addActivities($activities);        
    }
    
    /**
     *  Extract the times from the array, Picks up on key words such as
     *  Everyday, Weekend, Weekday, calls to extend times to build up
     *  an hourly array of activities.
     * 
     */
    private function extractTimes($times, $label)
    {
        foreach($times as $day => $times)
        {
            /**
             *  Handle keywords for Everyday, Weekday, Weekend.
             * 
             */            
            if(array_key_exists($day, $this->day_maps))
            {
                foreach($this->day_maps[$day] as $day)
                {
                    $this->format[$day] = array_merge($this->format[$day], $this->extendTimes($times, $label));
                    
                    // Order by time (key)
                    ksort($this->format[$day]);
                }
            }
            
            /**
             *  If it comes in already formated.
             * 
             */
            if(is_numeric($day))
            {
                $this->format[$day] = array_merge($this->format[$day], $this->extendTimes($times, $label));
                
                ksort($this->format[$day]);
            }
        }
    }
    
    /**
     *  For time ranges, Extends incrementally, for example, 
     *  17:00 => 19:00 would become 17:00, 18:00, 19:00 on an hourly
     *  increment. 
     * 
     */
    private function extendTimes($times, $label)
    {
        $format = array();
        
        foreach($times as $start => $end)
        {            
            $start_bomb = explode(':', $start);
            $end_bomb = explode(':', $end);
            
            $startDate = Carbon::createFromTime($start_bomb[0], $start_bomb[1], 0);
            $endDate   = Carbon::createFromTime($end_bomb[0], $end_bomb[1], 0);
            
            while($startDate->toTimeString() !== $endDate->toTimeString())
            {
                $format[$startDate->toTimeString()] = $label;
                
                // Increment start date
                $startDate->addHour();
            }
        }       
        
        return $format;
    }
    
    /**
     *  Adds specified activites, in the same format as everything else,
     *  This function has a little bit extra however, it will assign time
     *  to activities that dont have a specified time.
     * 
     */
    private function addActivities($activities)
    {
        foreach($activities as $name => $activity)
        {    
            if(empty($activity['times']) && is_numeric($activity['max']))
            {
                // Assign this [max] hours worth of time. 
                $activity['times'] = $this->assignSomeTime($name, $activity['max']);
            }           
            
            if(!empty($activity['times']))
            {
                $this->extractTimes($activity['times'], $name);
            }
        }
    }
    
    /**
     *  Assigns times to activities that only have hours specified,
     *  can also be used to assign times dynamically by not passing hours
     *  or passing NULL for hours.
     * 
     */
    private function assignSomeTime($name, $hours = NULL)
    {
        if(is_null($hours))
        {
            // Todo, Assume some hours here;
        }
        
        $timeLeft = $this->timeLeft();       
        
        $timeMap = $this->splitByDays($hours);
        
        return $this->assignTimeToDays($timeMap);
    }
    
    /**
     *  Takes hours and splits them by days, if we give this 10 hours say
     *  it will allocate some hours to each day or your least busiest day
     *  if theres not enough hours.
     * 
     */
    private function splitByDays($hours)
    {
        $timeLeft = $this->timeLeft();
        arsort($timeLeft);
        $timeAllocated = array(
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0
        );
        $key_order = array_keys($timeLeft);
        $finished = false;
        
        if(empty($key_order))
        {
            return $timeAllocated;
        }
        
        while(!$finished)
        {
            /**
             *  Some rules for this function:
             *  - put more time on the days that have more time to spare,
             *  - Where possible, put times in big chunks and spread the chunks out.
             */
            $hours_per_day = ceil($hours / count($key_order));
            
            if($hours_per_day < 2)
            {
                // Cut the days down.
                $hours_per_day = ceil($hours / (count($key_order) / 2));
            }
            
            $days = floor($hours / $hours_per_day);
            
            if($days > count($key_order))
            {
                $days = count($key_order);
            }
            
            for($i = 0;$i < $days;$i++)
            {
                
                if($timeLeft[$key_order[$i]] >= $hours_per_day)
                {                    
                    $timeAllocated[$key_order[$i]] = ($timeAllocated[$key_order[$i]] + $hours_per_day);
                    
                    $hours = ($hours - $hours_per_day);
                    
                    // Remove the timeleft also
                    $timeLeft[$key_order[$i]] = ($timeLeft[$key_order[$i]] - $hours_per_day);
                    
                } else 
                {
                    $timeAllocated[$key_order[$i]] = ($timeAllocated[$key_order[$i]] + $timeLeft[$key_order[$i]]);
                    
                    $hours = ($hours - $timeLeft[$key_order[$i]]);
                    
                    $timeLeft[$key_order[$i]] = 0;
                    
                    unset($key_order[$i]);                    
                }
            }
            
            $key_order = array_values($key_order);
            
            if($hours <= 0 || empty($key_order))
            {
                $finished = true;
            }          
        }
        
        return $timeAllocated;
    }
    
    /**
     *  Uses the times assigned from previous function to allocate
     *  times to days, ie set 17:00:00 => 18:00:00 for this activity.
     * 
     */
    private function assignTimeToDays($timeDayArr)
    {
        $entries = array();
        
        foreach($timeDayArr as $day => $hours)
        {
            if($hours > 0)
            {
                $available = $this->getAvailableTimes($day, $hours);
                
                $start = $available[0];
                
                $end = $available[$hours - 1];
                
                // Temp fix for missing hour :(
                $endBomb = explode(':', $end);
                $endDate = Carbon::createFromTime($endBomb[0], $endBomb[1], $endBomb[2]);
                $endDate->addHour();
                
                $entries[$day] = array($start => $endDate->toTimeString());
            }
        }
        
        return $entries;
    }
    
    /** 
     *  Returns an array of available times for a day, if you include
     *  the hasHours parameter it will make sure it only passes start
     *  times that have enough hours behind it to complete the task.
     * 
     */
    private function getAvailableTimes($day, $hasHours = NULL)
    {
        $allocatedToDay = $this->format[$day];
        $available = array();
        
        $start = Carbon::createFromTime(01, 00, 00);
        $end = Carbon::createFromTime(00, 00, 00);
        
        while($start->toTimeString() !== $end->toTimeString())
        {
            if(!array_key_exists($start->toTimeString(), $allocatedToDay))
            {
                $available[] = $start->toTimeString();
            }            
            
            $start->addHour();
        }
        
        if($hasHours !== NULL)
        {            
            for($i = 0;$i < $hasHours;$i++)
            {
                
            }
        }
        
        return $available;
    }
    
    /**
     *  Builds a count of hours left for each day.
     *  
     */
    private function timeLeft()
    {
        $times = array();
        
        for($i = 0; $i < 7; $i++)
        {
            if(24 - count($this->format[$i]) > 0)
            {
                $times[$i] = (24 - count($this->format[$i]));
            }
        }
        
        return $times;
    }
    
    
    
}
