<?php

/**
 * Block Wrapper for hole punching
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Wrapper_Links extends Mage_Page_Block_Template_Links
{
    /**
     * Initialize object
     *
     * @return void
     */

    private $_key = 'links';
    private $_cache_tag = true;

    public function __construct()
    {
        $this->setHtmlId($this->_key);
       	parent::__construct();
    }

    public function _afterToHtml($html)
    {

        if (stristr($this->getNameInLayout(), 'top') != false || get_class($this->getParentBlock()) == "Ezapps_Zoom_Block_Wrapper_Header")
                $maybe_top_block = true;
        else
                $maybe_top_block = false;

        if ($this->getCacheTag() && $maybe_top_block == true && 
	   ((trim($html) != "" && Mage::helper('ezzoom')->punchStatus($this->_key) == 1) || Mage::helper('ezzoom')->punchStatus($this->_key) == 2)) {
                $name = (Mage::helper('ezzoom')->getConfigData('zoom_lite') ? $this->getTemplate() : $this->getNameInLayout());
                return Mage::helper('ezzoom')->renderHoleStart($this->_key, $name) . parent::_afterToHtml($html) . Mage::helper('ezzoom')->renderHoleEnd($this->_key, $name);
        }
	else
		return parent::_afterToHtml($html);
    }

    public function setCacheTag($status) {
	
	$this->_cache_tag = $status;	
	return $this;

    }

    public function getCacheTag() {
	
	return $this->_cache_tag;	

    }

    public function addLinkBlock($blockName, $position_override = null)
    {
        $block = $this->getLayout()->getBlock($blockName);
	if ($position_override) {
        	$this->_links[$this->_getNewPosition((int)$position_override)] = $block;
	} else
        	$this->_links[$this->_getNewPosition((int)$block->getPosition())] = $block;
        return $this;
    }

    public function moveLinkByUrl($url, $new_position)
    {
        foreach ($this->_links as $k => $v) {
            if ($v->getUrl() == $url) {
                unset($this->_links[$k]);
		$this->_links[$this->_getNewPosition((int)$new_position)] = $v;
		ksort($this->_links);
            }
        }

        return $this;
    }



}
