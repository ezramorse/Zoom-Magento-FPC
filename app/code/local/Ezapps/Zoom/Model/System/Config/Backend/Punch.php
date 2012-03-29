<?php

/**
 * Zoom Page Cache Object
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Model_System_Config_Backend_Punch extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        $result = array();
        $result[] = array(
                'label' => "Auto",
                'value' => "1"
            );
        $result[] = array(
                'label' => "On",
                'value' => "2"
            );
        $result[] = array(
                'label' => "Off",
                'value' => "0"
            );

        return $result;
    }


}
