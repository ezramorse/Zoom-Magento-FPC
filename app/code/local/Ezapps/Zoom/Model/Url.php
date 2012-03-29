<?php
/**
 * Zoom Url Rewrite Modification
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */
class Ezapps_Zoom_Model_Url extends Mage_Core_Model_Url
{

    private $_no_reorder = true;

    public function getQuery($escape = false)
    {
	if (Mage::helper('ezzoom')->isEnabled() && $this->_no_reorder == true && Mage::registry('current_category') ) {
	        if (!$this->hasData('query')) {
	            $query = '';
	            $params = $this->getQueryParams();
	            if (is_array($params)) {
	                $query = http_build_query($params, '', $escape ? '&amp;' : '&');
	            }
	            $this->setData('query', $query);
	        }
	        return $this->_getData('query');
	} else return parent::getQuery($escape);
    }


    public function getUrl($routePath = null, $routeParams = null)
    {

	list($routePath, $routeParams, $this->_no_reorder) = Mage::helper('ezzoom')->processUrl($routePath, $routeParams);
	
	return parent::getUrl($routePath, $routeParams);

    }




}
