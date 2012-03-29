<?php

/**
 * Block Wrapper for hole punching
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Wrapper_Header extends Mage_Page_Block_Html_Header
{
    /**
     * Initialize object
     *
     * @return void
     */

    private $_key = 'header';
    private $_cache_tag = true;

    public function __construct()
    {
        $this->setHtmlId($this->_key);
       	parent::__construct();
    }

    public function getWelcome()
    {

	$welcome = parent::getWelcome();

        if ($this->getCacheTag() && ((trim($welcome) != "" && Mage::helper('ezzoom')->punchStatus($this->_key) == 1) || Mage::helper('ezzoom')->punchStatus($this->_key) == 2)) {
                $name = (Mage::helper('ezzoom')->getConfigData('zoom_lite') ? $this->getTemplate() : $this->getNameInLayout()) . ',getWelcome';
                return Mage::helper('ezzoom')->renderHoleStart($this->_key, $name) . $welcome . Mage::helper('ezzoom')->renderHoleEnd($this->_key, $name);
        }        
        else
		return $welcome;
    }

    public function setCacheTag($status) {
	
	$this->_cache_tag = $status;	
	return $this;

    }

    public function getCacheTag() {
	
	return $this->_cache_tag;	

    }
}
