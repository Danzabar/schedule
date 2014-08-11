<?php require_once dirname( __DIR__ ).'/vendor/autoload.php';

use \Danzabar\Schedule\Schedule;
use \Danzabar\Schedule\Helpers\Assumption;
use \Danzabar\Schedule\Helpers\DateStore;

Class ScheduleTest extends PHPUnit_Framework_TestCase
{
     
    /**
     *  Test DateStore -> splitByDays
     *  - Make sure it allocates all hours. 
     *  - Test what happens in the event that it runs out of hours.
     *  - Make sure it is blocking the activities up where it can.
     *  - Test its ability to add time to a single day
     * 
     */
    public function test_SplitByDays()
    {
        $dateStore = new DateStore();
        
        $timeLeft = $dateStore->getTimeLeft(FALSE);        
            
        // Allocate hours
        $test = $dateStore->splitByDays(14, $timeLeft);
        
        /**
         *  The date store should have allocated 
         *  2 hours to each day, since it has enough time to do this.
         */
        foreach($test as $day => $time)
        {
            $this->assertEquals(2, $time);
        }
        
        /**
         *  It should have also removed 2 hours from each day in the
         *  TimeLeft array
         * 
         */
        foreach($timeLeft as $day => $time)
        {
            $this->assertEquals(22, $time);
        }
        
        /**
         *  To test what happens when we have no time left, lets
         *  use an empty timeLeft array
         */
        $timeLeftEmpty = array();
        
        $test2 = $dateStore->splitByDays(14, $timeLeftEmpty);
        
        /**
         *  Not only do we expect no errors, but we need to know 
         *  theres 0 hours allocated in any day.
         * 
         */
        foreach($test2 as $day => $time)
        {
            $this->assertEquals(0, $time);
        }
        
        /**
         *  To make sure we are block activities up in pairs where possible,
         *  we need a new timeLeft, which gives a 3 hour slot on one day, and
         *  a 1 hour slot on the other, we will allocate 2 hours to the activity
         *  and assert that it put it in the larger available slot.
         */
        $timeLeftBlock = array(0 => 3, 1 => 1);
        
        $test3 = $dateStore->splitByDays(2, $timeLeftBlock);
        
        $this->assertEquals(2, $test3[0]);
    }
    
    /**
     *  Testing the dateStores hourToTime function for converting
     *  allocated hours to real time. 
     * 
     */
    public function test_hoursToTime()
    {
        $dateStore = new DateStore();
        
        // Just a basic test that it allocates the right amount of hours
        $hours = $dateStore->hoursToTime(0, 15);
        
        $start = array_keys($hours)[0];
        $end = array_values($hours)[0];
        
        $this->assertEquals( ($end - $start), 15);
    }
    
    /**
     *  Test the dateStores adding to daystore array.
     * 
     */    
    public function test_addToDay()
    {
        $dateStore = new DateStore();
        
        $dateStore->addTime('Test', 'Everyday', array('10:00' => '12:00'));
        $result = $dateStore->getStore();
        
        // Check all days are include
        $this->assertEquals(7, count($result));
        
        // Check that each day has the times allocated
        foreach($result as $day => $times)
        {
            $this->assertTrue(array_key_exists("10_hour", $times));
        }
    }
}
