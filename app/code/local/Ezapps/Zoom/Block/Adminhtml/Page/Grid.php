<?php

/**
 * Adminhtml Page Cache Listing Grid
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Adminhtml_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('ezzoomGrid');
      $this->setDefaultSort('hits');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ezzoom/page')->getCollection()->consolidateMisses();

      $this->setCollection($collection);
 
      parent::_prepareCollection();
      return $this;
  }

  protected function _prepareColumns()
  {  

      $this->addColumn('uri', array(
          	'header'    => Mage::helper('ezzoom')->__('URI'),
          	'align'     =>'left',
          	'index'     => 'uri',
      ));

      if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('ezzoom')->__('Store'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
	        'width' => '300px',
            ));
      }

      $this->addColumn('expires', array(
      		'header' => Mage::helper('ezzoom')->__('Expires'),
                'index' => 'expires',
                'type' => 'datetime',
                'width' => '150px',
      ));

      $this->addColumn('hits', array(
          	'header'    => Mage::helper('ezzoom')->__('Hits'),
          	'align'     =>'right',
          	'index'     => 'hits',
	  	'width' => '150px',
		'filter'    => false,
      ));

      $this->addColumn('action',  array('header'    => Mage::helper('ezzoom')->__('Action'),
                'width'     => '60px',
                'type'      => 'action',
                'getter'     => 'getId',
          	'align'     =>'center',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('Clear'),
                        'url'     => array('base'=>'*/adminhtml_ezzoom/clear'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
      ));

      return parent::_prepareColumns();

  }

  protected function _prepareMassaction() {
  	if(!$this->_userMode){
        	$this->setMassactionIdField('main_table.id');
                $this->getMassactionBlock()->setFormFieldName('page');

                $this->getMassactionBlock()->addItem('delete', array(
                	'label'    => $this->__('Delete'),
                	'url'      => $this->getUrl('*/adminhtml_ezzoom/massDelete'),
                	'confirm'  => $this->__('Are you sure?')
        	));
	}
	return $this;
  }

  public function getRowUrl($row)
  {
	return 'http' . ($row->getIsSecure() > 0 ? 's' : '') . '://' . $row->getServer() .  $row->getUri();
  }
 
}
