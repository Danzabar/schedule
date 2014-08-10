<?php namespace Danzabar\Schedule\Helpers;

/**
 *  The Date store class has taken over the role of
 *  adding/removing and calculating time left etc.
 *  
 *  The Assumption class will still pass it specific hours,
 *  this will just act as a library, which spreads the code 
 *  out a bit more and makes it less error prone/easier to test.
 * 
 */
Class DateStore
{
    /**
     *  Maps Keywords to numerical date values. 
     * 
     */
    protected $day_maps = array(
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

    
    protected $dateStore = array(
        0 => array(),
        1 => array(),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array()
    );
    
    protected $time_left;
 
    
    public function getStore()
    {
        return $this->dateStore;
    }
    
    /**
     *  Adds time to the store, accepts the raw data from
     *  the assumption class and converts it to a useful
     *  datestore array
     * 
     */
    public function addTime($label, $day, $times)
    {
        $days = $day;
        
        if(!is_array($day) && array_key_exists($day, $this->day_maps))
        {
            $days = $this->day_maps[$day];
        }
        
        $time_map = array();
        
        if(count($times) > 1)
        {
            foreach($times as $start => $end)
            {               
                $this->addToDay( $days, $this->extractTimes(array($start => $end), $label) );
            }
            
        } else 
        {
            
            $this->addToDay($days, $this->extractTimes($times, $label));
        }        
    }
    
    /**
     *  Add a time map to a day
     *  This should be a fully extracted time map. and Numerical day values.
     * 
     */
    public function addToDay($days, $times)
    {
        foreach($days as $day)
        {
            $this->dateStore[$day] = array_merge($times, $this->dateStore[$day]);
            
            // Sort the array by the array keys
            ksort($this->dateStore[$day], SORT_NUMERIC);
        }       
    }
    
    /**
     *  Get the time left for each dayk, or a specified day
     * 
     *  @param array $days - the days in their numeric values.
     */
    public function getTimeLeft($withTotal = TRUE, $days = NULL)
    {   
        if(is_null($days))
        {
            $days = $this->day_maps['Everyday'];
        }
        
        $time_left = array();
        
        if($withTotal)
        {
            $time_left['total'] = 0;
        }
        
        foreach($days as $day)
        {
            $hours  = ceil( 24 - count($this->dateStore[$day]) );
            
            if($hours > 0)
            {
                $time_left[$day] = $hours;
                
                if($withTotal)
                {
                    $time_left['total'] =  $time_left['total'] + $hours;
                }
            }          
        }
        
        return $time_left;
    }
    
  
    /** 
     *  Helper: Convert time to number.
     *  Converts a time to number format used by date store, eg 10:00 => 10;
     * 
     */
    public function timeToNumber($time) 
    {
        $frags = explode(":", $time);
        
        return intval($frags[0]);
    }    
    
    /**
     *  Takes hours and splits them by days, if we give this 10 hours say
     *  it will allocate some hours to each day or your least busiest day
     *  if theres not enough hours.
     * 
     */
    public function splitByDays($hours, &$timeLeft)
    {
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
     *  Using a set of hours for the day this function will return
     *  an array of times for that day that are available, and make up 
     *  the hours passed.
     * 
     */
    public function hoursToTime($day, $hours)
    {
        $selected_day_times = $this->dateStore[$day];
        $available_slots = array();
        $usable_slots = array();
        
        for($i = 0;$i < 24;$i++)
        {
            if(!isset($selected_day_times[$i."_hour"]))
            {
                $available_slots[$i] = '';
            }
        }
        
        // Now for each of the available dates, lets check they have our required hours behind them
        foreach($available_slots as $slot => $empty)
        {
            $can_use = true;
            
            for($x = $slot;$x < ($slot + $hours);$x++)
            {
                if(!isset($available_slots[$x]))
                {
                    $can_use = false;
                }
            }
            
            if($can_use)
            {
                $usable_slots[] = $slot;
            }
        }   
        
        // If the usable slots are empty, we do this hour by hour.
        if(empty($usable_slots))
        {
            $return_times = array();
            while(count($return_times) < $hours)
            {
                $index = array_rand($available_slots, 1);
                
                $start = $index;
                $end = $start + 1;
                
                $return_times[$start] = $end;
                
                unset($available_slots[$index]);
            }
            
            return $return_times;
        } else
        {
            // Pick a random start out of this
            $start = $usable_slots[array_rand($usable_slots, 1)];
            $end = $start + $hours;
            
            return array($start => $end);
        }      
    }   
        
    /**
     *  Private Helper : Convert a standard time array to a fully mapped out time array
     *  eg. [10:00] => 12:00 becomes 10, 11, 12 with labels. 
     * 
     */
    private function extractTimes($time_array, $label)
    {        
        $start = array_keys($time_array)[0];
        $finish = array_values($time_array)[0];
        
        if(strstr($start, ':'))
        {
            $start = $this->timeToNumber($start);
        }
        
        if(strstr($finish, ':'))
        {
            $finish = $this->timeToNumber($finish);
        }
        
        $mapped_arr = array();
        
        for($i = $start;$i < $finish; $i++)
        {
            $key = $i."_hour";
            $mapped_arr[$key] = $label;
        }
        
        return $mapped_arr;
    }
}
