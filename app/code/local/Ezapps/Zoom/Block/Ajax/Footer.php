<?php

/**
 * Footer Block With Ajax
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Ajax_Footer extends Mage_Core_Block_Template
{

    /**
     * Initialize object
     *
     * @return void
     */

    public function __construct()
    {
       	parent::__construct();
    }

    public function displayFooter() {

	if (Mage::helper('ezzoom')->isEnabled() && Mage::helper('ezzoom')->matchedPage())
		return true;
	else
		return false;
    }

    public function getAllData() {

        $result = array();
	
	$cache = $this->getCacheKeyInfo();

        $toolbar =  Mage::helper('ezzoom')->getToolbarState();

	$result['holes'] = $this->getHolesData();
	$result['url']   = $this->helper('core/url')->getCurrentUrl();
	$result['cache_key_info'] = $cache['storage_types'];
	$result['handles'] = Mage::getSingleton('core/layout')->getUpdate()->getHandles();
	$result['control'] = $toolbar['control'];
	if ($this->getProduct())
		$result['pid'] = $this->getProduct()->getId();

	return Mage::helper('ezzoom')->compress(json_encode($result));

    }

    public function getCurrencyJson() {

	return json_encode(Mage::helper('ezzoom')->getCurrencyInfo());

    }
	
    public function getCacheKeyInfo() {

	$str = Mage::getSingleton('core/layout')->getMessagesBlock()->getCacheKeyInfo();
	if ($str != '' && !is_array($str)) {
		return unserialize(Mage::getSingleton('core/layout')->getMessagesBlock()->getCacheKeyInfo());
	} else 
		return array('storage_types'=> array( 'core/session', 'customer/session', 'catalog/session' ));

    }

    public function getHolesData() {

	return Mage::helper('ezzoom')->getHolesData();
 
    }

    public function getHoles() {

	return Mage::helper('ezzoom')->getHoles();

    }

    public function getAjaxPre() {

	return Mage::helper('ezzoom')->getVarFromEzoomHandler('TAG_START');

    }

    public function getAjaxPost() {

	return Mage::helper('ezzoom')->getVarFromEzoomHandler('TAG_END');

    }

    public function getAjaxPreCurrency() {

	return Mage::helper('ezzoom')->getVarFromEzoomHandler('TAG_START_CURRENCY');

    }

    public function getAjaxPostCurrency() {

	return Mage::helper('ezzoom')->getVarFromEzoomHandler('TAG_END_CURRENCY');

    }

    public function getPageTag() {

	return Mage::helper('ezzoom')->getVarFromEzoomHandler('TAG_PAGE');

    }

    public function getFileTag() {

	return Mage::helper('ezzoom')->getVarFromEzoomHandler('TAG_FILE');

    }

    public function getProduct() {

	if (Mage::registry('current_product'))
		return Mage::registry('current_product');
	else
		return null;

    }

    public function getCurrencyConversion() {

	$session = Mage::getSingleton('customer/session');

	if (Mage::helper('ezzoom')->punchStatus('currency') > 0) {

        	$userCode = $session->getMaskedCurrency();
                $baseCode = Mage::app()->getStore()->getBaseCurrencyCode();

                if ($userCode != $baseCode)
			return true;

	}

	return false;

   }

   public function getEzappsLink () {

	$place_link = Mage::helper('ezzoom')->getConfigData('addlink', 'ezzoomlink');
	$home_page = array('cms_index_defaultindex', 'cms_index_index');


	if (Mage::helper('ezzoom')->isEnabled() && ($place_link > 0)) {
		
		$a = "EZAPPS Zoom: The Magento Full Page Cache";
		$t = "Zoom Full Page Cache and more Magento Products from EZAPPS";
		$link = "<div class=\"\" style=\"" . Mage::helper('ezzoom')->getConfigData('style', 'ezzoomlink')  . "\">" . 
			"<a href=\"http://www.ezapps.ca/\" title=\"$t\"><img src=\"//www.ezapps.ca/media/zoom-magento-full-page-cache.png\" border=\"0\" title=\"$t\" alt=\"$a\" /></a>" .
			"</div>";		

		if ($place_link == 1 && in_array($this->getAction()->getFullActionName(), $home_page))
			return $link;
		else if ($place_link == 2)
			return $link;

	} 

	return '';

   }


    function getPath() {

        return Mage::getSingleton('core/cookie')->getPath();

    }

    public function getDomain() {

        $domain = Mage::getSingleton('core/cookie')->getDomain();

        if (!empty($domain[0]) && ($domain[0] !== '.')) {
            $domain = '.'.$domain;
        }

	return $domain;

    }

    public function getCookieToSet() {

        $toolbar =  Mage::helper('ezzoom')->getToolbarState();

	$cookies = array();

	foreach ($toolbar['control'] as $key => $value)  
                $cookies[Mage::helper('ezzoom')->getVarFromEzoomHandler('GET_STEM').$key] = $value; 

        return $cookies;


    }



}
