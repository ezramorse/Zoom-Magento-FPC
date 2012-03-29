<?php

/**
 * EZAPPS Notification Observer
 *
 * @category   Ezapps
 * @package    Ezapps_Tools
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Tools_Model_Observer extends Mage_AdminNotification_Model_Observer
{
    /**
     * Predispath admin action controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function preDispatch(Varien_Event_Observer $observer)
    {

        if (Mage::getSingleton('admin/session')->isLoggedIn()) {

            $feedModel  = Mage::getModel('eztools/feed');

            $feedModel->checkUpdate();

        }

    }

    public function initFeed(Varien_Event_Observer $observer) {

	Mage::helper('eztools')->initAllFeedInfo();	

    }

}
