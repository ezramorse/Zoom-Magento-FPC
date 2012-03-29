<?php

/**
 * Zoom Page Resource Object
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Model_Mysql4_Page extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize object
     *
     * @return void
     */
    public function _construct()
    {
	$this->_init('ezzoom/ezapps_zoom_page', 'id');
    }

}
