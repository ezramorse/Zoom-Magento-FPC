<?php

/**
 * Zoom Page Cache Frontend Model
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Adminhtml_Form_Field_Get extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('variable', array(
            'label' => Mage::helper('adminhtml')->__('Variable'),
            'style' => 'width:55px',
        ));

        $this->addColumn('default', array(
            'label' => Mage::helper('adminhtml')->__('Default'),
            'style' => 'width:55px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add GET Variable');
        parent::__construct();
    }
}
