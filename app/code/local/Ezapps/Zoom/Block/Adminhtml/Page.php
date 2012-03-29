<?php

/**
 * Adminhtml Page Cache Listing
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize object
     *
     * @return void
     */
   public function __construct()
   {
     $this->_controller = 'adminhtml_page';
     $this->_blockGroup = 'ezzoom';
     $this->_headerText = Mage::helper('ezzoom')->__('Zoom Cache Manager');
     $this->_addButton('flush_magento', array(
            'label'     => Mage::helper('ezzoom')->__('Flush Page Cache'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_ezzoom/flush') .'\')',
            'class'     => 'delete',
        ));

     parent::__construct();

     $this->_removeButton('add');

   }
 
}
