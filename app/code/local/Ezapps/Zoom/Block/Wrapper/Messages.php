<?php

/**
 * Block Wrapper for hole punching
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Wrapper_Messages extends Mage_Core_Block_Messages
{
    /**
     * Initialize object
     *
     * @return void
     */

    private $_key = 'messages';
    private $_cache_tag = true;
    private $_rendered = false;

    public function __construct()
    {
        $this->setHtmlId($this->_key);
       	parent::__construct();
    }

    public function _afterToHtml($html)
    {
        if ($this->_rendered != true && $this->getCacheTag() && ((trim($html) != "" && Mage::helper('ezzoom')->punchStatus($this->_key) == 1) || Mage::helper('ezzoom')->punchStatus($this->_key) == 2)) {
		$name = (Mage::helper('ezzoom')->getConfigData('zoom_lite') ? $this->getTemplate() : $this->getNameInLayout());
		$this->_rendered = true;
       		return Mage::helper('ezzoom')->renderHoleStart($this->_key, $name) . parent::_afterToHtml($html) . Mage::helper('ezzoom')->renderHoleEnd($this->_key, $name);
	} else
		return parent::_afterToHtml($html);
    }

    public function GetGroupedHtml()
    {

	$html = parent::GetGroupedHtml();
        if ($this->_rendered != true && $this->getCacheTag() && ((trim($html) != "" && Mage::helper('ezzoom')->punchStatus($this->_key) == 1) || Mage::helper('ezzoom')->punchStatus($this->_key) == 2)) {
		$name = (Mage::helper('ezzoom')->getConfigData('zoom_lite') ? $this->getTemplate() : $this->getNameInLayout()) . ',getGroupedHtml';
		$this->_rendered = true;
       		return Mage::helper('ezzoom')->renderHoleStart($this->_key, $name) . parent::_afterToHtml($html) . Mage::helper('ezzoom')->renderHoleEnd($this->_key, $name);
	} else
		return $html;
    }

    public function setCacheTag($status) {
	
	$this->_cache_tag = $status;	
	return $this;

    }

    public function getCacheTag() {
	
	return $this->_cache_tag;	

    }
}
