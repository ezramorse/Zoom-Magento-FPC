<?php

/**
 * Zoom Currency Overridder 
 *
 * @author      Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

require_once("Mage/Directory/controllers/CurrencyController.php");

class Ezapps_Zoom_CurrencyController extends Mage_Directory_CurrencyController
{
    public function switchAction()
    {
	if (Mage::helper('ezzoom')->isEnabled() && Mage::helper('ezzoom')->punchStatus('currency') > 0) {

	        if ($currency = $this->getRequest()->getParam('currency'))
		    Mage::getSingleton('customer/session')->setMaskedCurrency($currency);


		if (Mage::getSingleton('customer/session')->getLastEzzoomUrl() != '')
			$this->getResponse()->setRedirect(Mage::getSingleton('customer/session')->getLastEzzoomUrl());
		else
			$this->getResponse()->setRedirect(Mage::getBaseUrl());

	} else parent::switchAction();
    }
}
