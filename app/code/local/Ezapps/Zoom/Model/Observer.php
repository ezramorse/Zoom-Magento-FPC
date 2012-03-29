<?php

/**
 * Zoom Page Observer
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Model_Observer extends Mage_Core_Model_Abstract
{
    public function clearProduct($product_event) {

	$product = $product_event->getProduct()->getOrigData();

        if ($product['store_id'] != 0)
                $stores[] = $product['store_id'];
        else
                foreach (Mage::getModel('core/store')->getCollection() as $store)
                        $stores[] = $store->getId();

        foreach ($stores as $store_id) {

                $uris = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('product_id', array('eq' => $product['entity_id']));

		foreach ($uris as $uri) {
			Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/" . $uri->getRequestPath()))->delete();
		}				

                $uris = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('target_path', array('eq' => "catalog/product/view/id/" . $product['entity_id']));

                foreach ($uris as $uri) {
                        Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/" . $uri->getRequestPath()))->delete();
                }

                Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/catalog/product/view/id/" . $product['entity_id']))->delete();

                Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('like' => "/catalog/product/view/id/" . $product['entity_id'] . '/%'))->delete();

		$uris = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('target_path', array('eq' => "review/product/list/id/" . $product['entity_id']));

                foreach ($uris as $uri) {
                        Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/" . $uri->getRequestPath()))->delete();
                }

                Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/review/product/list/id/" . $product['entity_id']))->delete();
		
		Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('like' => "/review/product/list/id/" . $product['entity_id'] . '/%'))->delete();



	}

    }

    public function clearCategory($category_event) {

        $category = $category_event['category'];

        $stores = array();

	if ($category->getStoreId() != 0)
		$stores[] = $category->getStoreId();
	else
        	foreach (Mage::getModel('core/store')->getCollection() as $store)
                	$stores[] = $store->getId();
        
        foreach ($stores as $store_id) {

                $uris = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('category_id', array('eq' => $category->getId()));

                foreach ($uris as $uri) {
                        Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/" . $uri->getRequestPath()))->delete();
                }
	
		$uris = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('target_path', array('eq' => 'catalog/category/view/id/' . $category->getId()));		

		foreach ($uris as $uri) {
                        Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/" . $uri->getRequestPath()))->delete();
                }

		Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/catalog/category/view/id/" . $category->getId()))->delete();		

		Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('like' => "/catalog/category/view/id/" . $category->getId() . '/%'))->delete();		
        }

    }

    public function clearCMS($cms_page) {

	$orig_page = $cms_page->getPage()->getOrigData();
	$stores = $orig_page['store_id'];
	if (count($stores) == 1 && $stores[0] == 0) {
		$stores = array();
		foreach (Mage::getModel('core/store')->getCollection() as $store) {
			$stores[] = $store->getId();
		}
	}
	
	foreach ($stores as $store_id) {

		Mage::getModel('ezzoom/page')->getCollection()
                        ->addFieldToFilter('store_id', array('eq' => $store_id))
                        ->addFieldToFilter('uri', array('eq' => "/{$orig_page['identifier']}"))->delete();
		
		$id = Mage::getStoreConfig('web/default/cms_home_page', $store_id);		

		if ($id == $orig_page['identifier']) {
			 Mage::getModel('ezzoom/page')->getCollection()
	                        ->addFieldToFilter('store_id', array('eq' => $store_id))
        	                ->addFieldToFilter('uri', array('eq' => "/"))->delete();
		}

                $uris = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('target_path', array('eq' => 'cms/page/view/page_id/' . $orig_page['page_id']  ));

                foreach ($uris as $uri) {
                        Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => "/" . $uri->getRequestPath()))->delete();
                }
	
                Mage::getModel('ezzoom/page')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $store_id))
                                ->addFieldToFilter('uri', array('eq' => 'cms/page/view/page_id/' . $orig_page['page_id']))->delete();

			
	}

    }

}
