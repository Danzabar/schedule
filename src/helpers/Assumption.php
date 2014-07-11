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
        
        $this->day_maps = array(
            'Weekday'   => array(1,2,3,4,5),
            'Everyday'  => array(0,1,2,3,4,5,6),
            'Weekend'   => array(0,6)
        );
    }
    
    /** 
     *  Builds the settings variable based on the settings it receives from
     *  the Schedule class.
     * 
     */
    public function process($excludes, $work_hours, $activities, $increments)
    {
        $formatted_arr = $this->formatArr($excludes, $work_hours, $activities, $increments);
        
    }
    
    private function formatArr($excludes, $work_hours, $activities, $increments)
    {
        $format = array(
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
            
        }
    }
    
    private function extractTimes($times)
    {
        $extraction = array();
        
        foreach($times as $day => $times)
        {
            
        }
    }
    
    private function timeLeft()
    {
        
        
    }
    
    
    
}
