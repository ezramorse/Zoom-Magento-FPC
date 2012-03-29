<?php

/**
 * Zoom Page Cron Model
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Model_Cron extends Mage_Core_Model_Abstract
{

     protected static $has_ran = false;

     public static function cleanCache() {

               if(self::$has_ran != true && self::isLocked() != true){
			Mage::helper('ezzoom')->flushCache(time());
			Mage::app()->removeCache('ezzoom_cron_lock');
			self::$has_ran = true;
               } 

     }

     public static function isLocked(){
                $time = Mage::app()->loadCache('ezzoom_cron_lock');
                if ($time){
                        if((time() - $time) > 1200) 
				Mage::app()->removeCache('ezzoom_cron_lock');

                        return true;
                }
        
                Mage::app()->saveCache(time(), 'ezzoom_cron_lock', array(), 1200);
                return false;
     }


}
