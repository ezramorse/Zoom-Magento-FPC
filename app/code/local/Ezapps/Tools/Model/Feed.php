<?php

/**
 * EZAPPS Feed Model
 *
 * @category   Ezapps
 * @package    Ezapps_Tools
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Tools_Model_Feed extends Mage_AdminNotification_Model_Feed 
{
    const XML_USE_HTTPS_PATH    = 'eztools/adminnotification/use_https';
    const XML_FEED_URL_PATH     = 'eztools/adminnotification/feed_url';
    const XML_FREQUENCY_PATH    = 'eztools/adminnotification/frequency';
    const XML_LAST_UPDATE_PATH  = 'eztools/adminnotification/last_update';


    public function getLastUpdate()
    {
        return Mage::app()->loadCache('eztools_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'eztools_notifications_lastcheck');
        return $this;
    }


   public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . Mage::getStoreConfig(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }



}
