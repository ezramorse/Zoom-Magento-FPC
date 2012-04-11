<?php

/**
 * Zoom Hole Punching Controller
 *
 * @author      Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_HoleController extends Mage_Core_Controller_Front_Action
{

	public function convertAction() {

                $result = Mage::helper('ezzoom')->getCurrencyInfo();		

 	        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

	}

        public function fillAction() {

		$data = json_decode(Mage::helper('ezzoom')->decompress($this->getRequest()->getParam('holes')), true);

		$session = Mage::getSingleton('customer/session');

                if (trim($data['url']) != '') {
                        $session->setLastEzzoomUrl($data['url']);
                        $url = parse_url($data['url']);
                        $_SERVER['HTTPS'] = ($url['scheme'] == 'https' ? 'on' : 'off');
                        $_SERVER['HTTP_HOST'] = $url['host'];
                        $_SERVER['REQUEST_URI'] = $url['path'];
                }

		$page = Mage::getModel('ezzoom/page')->load($this->getRequest()->getParam('id'));

		if ($page->getFilename() != '') {
			$page->setHits($page->getHits()+1)->save();
		} else {
			Mage::helper('ezzoom')->deleteFile(Mage::helper('ezzoom')->decompress($this->getRequest()->getParam('key')));
		}

		Mage::helper('ezzoom')->setMatchedPage(false);

		$update = Mage::getSingleton('core/layout')->getUpdate();

		$accept = array("default", "STORE_", "THEME_", "customer_logged_out");

		foreach ($data['handles'] as $handle)
				$update->addHandle("ezzoom_" . $handle);

		$this->loadLayout();

		foreach ($data['cache_key_info'] as $cache) {
            		$this->_initLayoutMessages($cache);
		}

		$result = Mage::helper('ezzoom')->getCurrencyInfo();

		$hole_filling = array();

		if (!Mage::getSingleton('catalog/session')->getParamsMemorizeDisabled())
			foreach ($data['control'] as $key => $value)
				$session->setData($key, $value);

		foreach ($data['holes'] as $module => $blocks) {

			foreach ($blocks as $key => $block_name) {

				if ($module == 'poll') { 
	                        	        if (!Mage::getSingleton('core/session')->getJustVotedPoll())
         	 	      	                	Mage::getSingleton('core/session')->setJustVotedPoll(Mage::getSingleton('core/session')->getZoomRecentVote());
				}

                                try {
                                        $params = explode(",", $block_name);
                                        $block_name = $params[0];
                                        $block_object = Mage::getSingleton('core/layout')->getBlock($block_name);
                                                        if (is_object($block_object) && method_exists($block_object, 'setCacheTag'))  {
								if ($block_name == 'global_messages') {
								} else if (array_key_exists(1, $params)) {
                                                                        $hole_filling[] = array('block' => $key, 'data' => $block_object->setCacheTag(false)->{$params[1]}() );
                                                                } else
                                                                        $hole_filling[] = array('block' => $key, 'data' => $block_object->setCacheTag(false)->toHTML()); 
	

                                                        } else if (Mage::helper('ezzoom')->getDebugAjax())
                                                                $hole_filling[] = array('block' => $key, 'data' => "Cannot locate block: $block_name");
	
                                } catch(Exception $e){
                                        if (Mage::helper('ezzoom')->getDebugAjax())
                                                $hole_filling[] = array('block' => $key, 'data' => $e->getMessage());
                                }

				if ($module == 'poll') {
        	                        	Mage::getSingleton('core/session')->setZoomRecentVote(false);
	                                	Mage::getSingleton('core/session')->getJustVotedPoll(false);
				}

                        }

                }

		$result['fill'] = $hole_filling; 

		if (array_key_exists('pid', $data)) {


			$product_index = Mage::getModel('reports/product_index_viewed')->getCollection()
						->addFieldToFilter('store_id',   array('eq' => Mage::app()->getStore()->getId()))
						->addFieldToFilter('product_id', array('eq' => $data['pid']));

			if ($session->isLoggedIn()) {

				$product_index->addFieldToFilter('customer_id', array('eq' => $session->getCustomerId()));
								
				if (count($product_index) < 1)
                                Mage::getModel('reports/product_index_viewed')
                                            ->setCustomerId($session->getCustomerId())
                                            ->setProductId($data['pid'])
                                            ->setStoreId(Mage::app()->getStore()->getId())
                                            ->save()
                                            ->calculate();

			} else {

				$product_index->addFieldToFilter('visitor_id', array('eq' => Mage::getSingleton('log/visitor')->getId()));


				if (count($product_index) < 1)
				Mage::getModel('reports/product_index_viewed')
					    ->setVisitorId(Mage::getSingleton('log/visitor')->getId())
					    ->setCustomerId(null)
       					    ->setProductId($data['pid'])			
					    ->setStoreId(Mage::app()->getStore()->getId())
        				    ->save()
        				    ->calculate(); 
			}

		}

		$this->getResponse()->setBody($this->getRequest()->getParam('callback') . '(' . Mage::helper('core')->jsonEncode($result) . ')');
		$this->getResponse()->setHeader('content-type', 'application/x-javascript', true); 

        }
}
