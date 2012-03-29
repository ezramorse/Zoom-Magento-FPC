<?php

/**
 * Adminhtml dashboard diagram tab helper for custom graphs
 *
 * @category   Ezapps
 * @package    Ezapps_Tools
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Tools_Block_Adminhtml_Dashboard_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _prepareLayout()
    {
        $this->addTab('orders', array(
            'label'     => $this->__('Orders'),
            'content'   => $this->getLayout()->createBlock('adminhtml/dashboard_tab_orders')->toHtml(),
            'active'    => true
        ));

        $this->addTab('amounts', array(
            'label'     => $this->__('Amounts'),
            'content'   => $this->getLayout()->createBlock('adminhtml/dashboard_tab_amounts')->toHtml(),
        ));


	$modules = Mage::getConfig()->getNode('modules')->children();
	$modulesArray = (array)$modules;

	if(array_key_exists('Ezapps_Zoom', $modulesArray) && $modulesArray['Ezapps_Zoom']->is('active')) {
	        $this->addTab('zoom', array(
	            'label'     => $this->__('Zoom Cache'),
	            'content'   => $this->getLayout()->createBlock('ezzoom/adminhtml_dashboard_tab_zoom')->toHtml(),
		    'disable_ajax' => true,
	        ));		
	}

        return parent::_prepareLayout();
    }


    public function getTabsIds()
    {
	$holder = array();
	foreach ($this->_tabs as $key => $tab) 
		if ($tab->getData('disable_ajax') != true)
			$holder[] = $key;
	return $holder;

    }

}
