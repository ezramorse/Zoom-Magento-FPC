<?php

/**
 * Zoom Adminhtml Cache Listing Controller
 *
 * @author      Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Adminhtml_EzzoomController extends Mage_Adminhtml_Controller_Action
{

        protected function _initAction() {
                $this->loadLayout()
                        ->_addBreadcrumb(Mage::helper('adminhtml')->__('Zoom Cache Manager'), Mage::helper('adminhtml')->__('Zoom Cache Manager'));

                return $this;
        }

        public function indexAction() {
                $this->_initAction()
                        ->renderLayout();
        }

	public function clearAction() {

		$id = $this->getRequest()->getParam('id');
		Mage::helper('ezzoom')->clearCache($id);
		Mage::getSingleton('adminhtml/session')->addSuccess(
                	Mage::helper('adminhtml')->__(
                        	'Page successfully cleared'
                        )
                );
		$this->_redirect('*/*/index');
	}

	public function flushAction() {

		Mage::helper('ezzoom')->flushCache();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Cache successfully cleared'
                        )
                );
                $this->_redirect('*/*/index');	
	}

 	public function massDeleteAction() {

		$pages = $this->getRequest()->getPost('page');
                if(!is_array($pages)) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
                } else {
                        try {
                                foreach ($pages as $id) {
					Mage::helper('ezzoom')->clearCache($id);
                                }
                                Mage::getSingleton('adminhtml/session')->addSuccess(
                                        Mage::helper('adminhtml')->__(
                                                'Total of %d record(s) were successfully deleted', count($pages)
                                        )
                                );
                        } catch (Exception $e) {
                                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        }
                }
		$this->_redirect('*/*/index');
	}
}
