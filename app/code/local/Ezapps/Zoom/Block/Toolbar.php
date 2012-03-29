<?php

/**
 * Toolbar Extender
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    public function getDefaultOrder()
    {
	    return $this->_orderField;
    }

    public function getDefaultDirection()
    {
            return $this->_direction;
    }



}
