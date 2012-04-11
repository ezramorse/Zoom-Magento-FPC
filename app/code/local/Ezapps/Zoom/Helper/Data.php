<?php

/**
 * Zoom data helper
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Helper_Data extends Mage_Core_Helper_Abstract 
{

	private $_holes 		= array();
	private $_key			= 'ezzoom_general';
	private $_config		= array();
	private	$_save_rewrites		= array();
	private $_matched_page		= null;
	private $_ttl			= 0;

	private $_rewrite_package	= '';
	private $_rewrite_template	= '';
	private $_rewrite_theme		= '';
	private $_render_time		= 0;
	private $_file_name		= 0;		
	private $_zoom_handler_data	= array();
	private $_zoom_handler_backup	= null;
	private $_helper_toolbar	= null;
	private $_helper_toolbar_state	= null;
	private $_cache_match		= true;
	private $_tool_bar_finder	= true;

	public function getCurrencyInfo() {

                $session = Mage::getSingleton('customer/session');

                $result = array();

		if ($this->getConfigData('punch_currency', 'holepunch') > 0) {
                        $userCode = $session->getMaskedCurrency();
                        $baseCode = Mage::app()->getStore()->getBaseCurrencyCode();
                        if ($userCode != '' && $userCode != $baseCode) {
                                $userCurrency = Mage::getModel('directory/currency')->load($userCode);
                                $baseCurrency = Mage::app()->getStore()->getBaseCurrency();

                                $js_string = Mage::app()->getLocale()->getJsPriceFormat();
                                $js_string['pattern'] = $userCurrency->getOutputFormat();
                                $result['conversionRate']   = $baseCurrency->convert(1.00, $userCurrency);
                                $result['userTemplate']     = str_replace('%s', '#{price}', $userCurrency->getOutputFormat());
                                $result['format']           = $js_string;
                        }
                }

		return $result;

	}

	public function processUrl($routePath = null, $routeParams = null) {


                $no_reorder = false;
	
		if ($this->matchedPage() && $this->_tool_bar_finder && $routePath == '*/*/*' && ((is_object(Mage::getSingleton('core/layout')->getBlock("product_list_toolbar")) 
		    || Mage::registry('current_category')) && !Mage::registry('current_product'))) {

	               	$state = $this->getToolbarState();					

	                if ($state && ($this->getConfigData('normalize_urls') || $this->getConfigData('friendly_urls'))) {
				

                	        $merge_controls  = false;
                	        $default_state   = true;

				if (!array_key_exists('_query', $routeParams))
					$routeParams['_query'] = array();

       	                        list($page_name) =  array_keys($state['page']);
       	                        if (array_key_exists($page_name, $routeParams['_query']) && is_null($routeParams['_query'][$page_name]))
       	                                 $routeParams['_query'][$page_name] = 1;

				$params = array_merge($state['page'], $state['control']);				

				foreach ($state['filters'] as $key => $value) {
					if (array_key_exists($key, $routeParams['_query'])) {

						$params[$key] = $routeParams['_query'][$key];
						$merge_controls  = true;
					} else if (!empty($value)) {
						$params[$key] = $value; 
					}
				}

				$routeParams['_query'] = array_merge($params, $routeParams['_query']);					


				foreach ($state['default'] as $key => $value)
					if ($value != $routeParams['_query'][$key]) {
						$default_state = false;
						break;
					}

				if ($default_state == true)
					foreach ($state['default'] as $key => $value)
                                        	if ($value != $state['control'][$key]) {
                                        	        $default_state = false;
                                        	        break;
                                        	} 

				if ($default_state == true && !$merge_controls) {
					if (array_key_exists($page_name, $routeParams['_query'])
                                          && $routeParams['_query'][$page_name] == 1)
						$routeParams['_query'][$page_name] = null;
					else
						$routeParams['_query'] = array_merge($state['page'], $routeParams['_query']);

                                                foreach ($state['control'] as $key => $value)
                                                    $routeParams['_query'][$key] = null;

                                                foreach ($state['filters'] as $key => $value)
                                                    $routeParams['_query'][$key] = null;
				} 

				$no_reorder = true;
				$routeParams['_current'] = false;

				if (count($routeParams['_query']) < 1)
					unset($routeParams['_query']);

				if ($this->getConfigData('friendly_urls')) {
				
					$query = "";
					foreach ($routeParams['_query'] as $key => $value) {
						if (!is_null($value)) {
							if (!is_array($value))
								$query .= '/' . $key . '/' . $value;
							else
								$query .= '/' . $key . '/' . implode(",", $value);
						}
						$routeParams['_query'][$key] = null;
					}
				
					$uri = $this->getVarFromEzoomHandler('base_uri'). (strlen($query) > 0 ? $this->getVarFromEzoomHandler('GET_URI_MARK') . $query : '');
					if (substr($uri, 0, 1)=='/')
						$uri = substr($uri, 1);

					$routeParams['_direct'] = str_replace('//', '/', $uri);
					$routeParams['_nosid'] = true;

				}

                	}

		}

       		return array($routePath, $routeParams, $no_reorder);

	}

	public function getHelperToolbar() {
		
		if (is_null($this->_helper_toolbar))
			$this->_helper_toolbar = Mage::getSingleton('core/layout')->getBlock("product_list_toolbar");

                if (!is_object($this->_helper_toolbar))
			$this->_helper_toolbar = Mage::getSingleton('core/layout')->createBlock('catalog/product_list_toolbar')->setNameInLayout('helperbar');

		return $this->_helper_toolbar;

	}

	public function getToolbarState() {

		if (is_null($this->_helper_toolbar_state)) {

	                $toolbar = $this->getHelperToolbar();

			$mode  = explode("-", Mage::getStoreConfig('catalog/frontend/list_mode'));
			$limit = Mage::getStoreConfig("catalog/frontend/{$mode[0]}_per_page");

		
			$control = array(
               	         	$toolbar->getDirectionVarName() => $toolbar->getCurrentDirection(),
               		        $toolbar->getLimitVarName()     => $toolbar->getLimit(),
        	                $toolbar->getModeVarName()      => $toolbar->getCurrentMode(),
        	                $toolbar->getOrderVarName()     => $toolbar->getCurrentOrder());

			$default = array(
               	         	$toolbar->getDirectionVarName() => $toolbar->getDefaultDirection(),
               		        $toolbar->getLimitVarName()     => $toolbar->getDefaultPerPageValue(),
        	                $toolbar->getModeVarName()      => $mode[0],
        	                $toolbar->getOrderVarName()     => $toolbar->getDefaultOrder());

			if ( Mage::registry('current_category') && Mage::registry('current_category')->getDefaultSortBy() != '' )
                                $default[$toolbar->getOrderVarName()] = Mage::registry('current_category')->getDefaultSortBy();

			$page = array($toolbar->getPageVarName() => ($toolbar->getCurrentPage() < 1 ? 1 : $toolbar->getCurrentPage()));


        		$collection = Mage::getResourceModel('catalog/product_attribute_collection');
        		$collection
       			     ->setItemObjectClass('catalog/resource_eav_attribute')
        		     ->addStoreLabel(Mage::app()->getStore()->getId())
        		     ->setOrder('position', 'ASC')
			     ->addIsFilterableFilter();

        		$attributes = $collection->load();

			$active_filters = array();
			foreach (Mage::getSingleton('catalog/layer')->getState()->getFilters() as $filter) {
				$active_filters[$filter->getFilter()->getRequestVar()] = $filter->getValue();
			}


			$filters = array('cat'=>null);

	                foreach ($attributes as $attribute)
        	                $filters[$attribute->getattributeCode()] = null;
			
			foreach ($filters as $key => $value) {
				if (array_key_exists($key, $active_filters))
					if (is_array($active_filters[$key]))
						$filters[$key] = implode(",", $active_filters[$key]);
					else
						$filters[$key] = $active_filters[$key];
			}

			ksort($control);
			ksort($default);
			ksort($page); 
			ksort($filters);

			$this->_helper_toolbar_state = array('control' =>  $control, 'page' => $page, 'default'=> $default, 'filters' => $filters);
		} 
		
		return $this->_helper_toolbar_state;
	}


	public function getVarFromEzoomHandler($name) {
		if (count($this->_zoom_handler_data) < 1 && class_exists('Ezapps_Zoom_Handler')) {
			$zoom = Ezapps_Zoom_Handler::getInstance();
			$this->_zoom_handler_data = $zoom->getVars();
		}

		if (array_key_exists($name, $this->_zoom_handler_data))
			return $this->_zoom_handler_data[$name];
		else return null;
	}

	public function generateClientRewrites() {

		if (Mage::app()->getLayout()->getArea() != 'frontend')
			return;

		$toolbar = $this->getToolbarState();

		if (is_null($toolbar))
			return;

		$toolbar_helper = $this->getHelperToolbar();		

		$toolbar['default'][$toolbar_helper->getOrderVarName()] = $toolbar_helper->getDefaultOrder();

                foreach (Mage::getModel('core/store')->getCollection() as $store)
                        $stores[] = $store->getId();

                foreach ($stores as $store_id) {

                        $data = array('package'   => Mage::getStoreConfig("design/package/ua_regexp",  $store_id),
                                      'template'  => Mage::getStoreConfig("design/theme/template_ua_regexp", $store_id),
                                      'theme'     => Mage::getStoreConfig("design/theme/default_ua_regexp",    $store_id),
				      'controls'  => $toolbar);

                        $url    = parse_url(Mage::getStoreConfig("web/unsecure/base_url", $store_id));
                        $urlssl = parse_url(Mage::getStoreConfig("web/secure/base_url",   $store_id));

			$file = DS . $this->getVarFromEzoomHandler('STORE') . DS . $this->getVarFromEzoomHandler('ZOOM_CLIENT_MATCH_DATA');
	 
			Mage::helper('ezzoom')->saveFile($file, json_encode($data), true);

			if (function_exists('apc_add')) {
				if (apc_exists($this->getVarFromEzoomHandler('APC_KEY') . $file))
					apc_delete($this->getVarFromEzoomHandler('APC_KEY') . $file);

				apc_add($this->getVarFromEzoomHandler('APC_KEY') . $file, $data);
			}


                }
	}
	
	public function setRewrites($field, $value) {
	
		$this->_save_rewrites[$field] = $value;

	}

	public function getRewrites() {

		return $this->_save_rewrites;		

	}

	public function getConfigData($field, $code = "basicsettings", $store_id=null)
    	{
        	$path = $this->_key.'/'.$code.'/'.$field;

		if (!array_key_exists($path, $this->_config))
			$this->_config[$path] = Mage::getStoreConfig($path);

		return $this->_config[$path];
        }
	
	public function clearCache($id) 
	{

		$page = Mage::getModel('ezzoom/page')->load($id);
		$collection = Mage::getModel('ezzoom/page')->getCollection()
			->addFieldToFilter('store_id', array('eq' => $page->getStoreId()))
			->addFieldToFilter('uri', array('eq' => $page->getUri()));

		$collection->delete();
	
	}

	public function flushCache($time = 0) {

		if ($time == 0) {
			$collection = Mage::getModel('ezzoom/page')->getCollection();
		} else {

			$from = Mage::getSingleton('core/date')->date(null, $time-(84000*365*2));
			$to = Mage::getSingleton('core/date')->date(null, $time);

			$collection = Mage::getModel('ezzoom/page')->getCollection()
					->addFieldToFilter('expires', array('to' => $to, 'from' => $from));
		}
	
                $collection->delete();

	}
	
	public function fetchHoleRecord($main_key, $main_template) {

		if (array_key_exists($main_key, $this->_holes)) {
			$repeat_hole = false;
			foreach ($this->_holes[$main_key] as $key => $template) {
				if ($main_template==$template) {
					$repeat_hole = true;
				}	
			}

			if (!$repeat_hole) {
				$key = $main_key . (count($this->_holes[$key]) + 1);
				$this->_holes[$main_key][$key] = $main_template;
			}
			
			return $key;

		} else {
			$this->_holes[$main_key] = array();
			$this->_holes[$main_key][$main_key] = $main_template;
			return $main_key;
		}

	}

	public function renderHoleStart($key, $template) 
	{
	
		$key = $this->fetchHoleRecord($key, $template);

 		if ($this->getDebugProfile())
			$prefix = "<div style=\"border:solid 1px #444444\"><div style=\"border:solid 2px #6F8992\">" . 
		    		  "<div style=\"background-color:#6F8992;color:white;font-weight:bold\">ezzoom-{$key}</div>";
		else $prefix = "";

		return "{$prefix}<span id=\"" . $this->genClass($key)  . "\">" . $this->getHoleStartTag($key);	
		
	}

	public function renderHoleEnd($key, $template) 
	{

		$key = $this->fetchHoleRecord($key, $template);

 		if ($this->getDebugProfile()) 
			$postfix = "</div></div>";
		else $postfix = "";
	
		return $this->getHoleEndTag($key) . "</span>{$postfix}";	
		
	}

	private function genClass($key) 
	{

		return "ezzoom-{$key}";

	}

        private function getHoleStartTag($key) 
	{

                return $this->getVarFromEzoomHandler('HOLE_START') . $this->genClass($key) . $this->getVarFromEzoomHandler('HOLE_END_PRE');

        }

        private function getHoleEndTag($key) 
	{

		return $this->getVarFromEzoomHandler('HOLE_START') . $this->genClass($key) . $this->getVarFromEzoomHandler('HOLE_END_POST');

        }

	public function renderHeaderDebug($buffer) {

		if (Mage::app()->getLayout()->getBlock('root')) {	
			if (Mage::app()->getLayout()->getBlock('root')->getTemplate() != "") {

				if ($this->matchedPage() == 1)
					$buffer = $this->getHeaderIncluded() . $buffer;
			}
		}

		return $buffer;

	}

	public function punchHoles ($buffer) 
	{

                $fill = $this->getVarFromEzoomHandler('DEFAULT_FILL');

                $code = Mage::app()->getStore()->getCode();
                if (is_array($fill) && array_key_exists($code, $fill))
                        $fill = $fill[$code];

                $search = array('{{secure_url}}', '{{unsecure_url}}');
                $replace = array(Mage::getUrl('',array('_secure'=>true)), Mage::getUrl('',array('_secure'=>false)));

		foreach ($this->getHoles() as $key) {

			$start = $this->getHoleStartTag($key);
			$end   = $this->getHoleEndTag($key);		

                       if (array_key_exists($key, $fill)) {
                               $default = str_replace($search, $replace, $fill[$key]);
                       } else
                               $default = '';

                       $buffer = preg_replace("#$start(.*)$end#sU", $default, $buffer);
		}

		return $buffer;

	}

	public function setRenderTime($time) {
		
		$this->_render_time = $time;

	}

	public function getHeaderIncluded() {
		$header = trim($this->getConfigData('header_string_include', 'debug'));
		if ($header != '') {
			$search = array('%SYSTIME', '%GENTIME%', '%FILE%');
			$replace = array(date("r", Mage::getModel('core/date')->timestamp(time())), $this->_render_time, $this->_file_name);
			return str_replace($search, $replace, $header);
		} else return false;
	}

	public function getDebugAjax() 
	{

		return $this->getConfigData('ajax_debug', 'debug');
	}

	public function getDebugProfile() 
	{

		return $this->getConfigData('hole_punch_profile', 'debug');
	}

	public function getHolesData() 
	{

		return $this->_holes;

	}
	
	public function compress($ajax_state) {
		return base64_encode(addslashes(gzcompress($ajax_state, 6)));
	}

	public function decompress($ajax_state) {
		return gzuncompress(stripslashes(base64_decode($ajax_state)));		
	}

	public function getHoles() 
	{
		$holes = array();

		foreach ($this->_holes as $module) {
			foreach ($module as $key => $template) {
				$holes[] = $key;
			}
		}

		return $holes;

	}

	public function setMatchedPage($value) {
		
		if ($this->isEnabled() == true)
			$this->_matched_page = $value;

	}

	public function setToolBarFinder($value) {
		$this->_tool_bar_finder = $value;
	}

	public function getToolBarFinder($value) {
		return $this->_tool_bar_finder;
	}

	public function matchedPage() {

		if (!class_exists('Ezapps_Zoom_Handler')) {
		
			$this->_matched_page = false;
                                return false;

		}
		
		if (is_null($this->_matched_page)) {

			if ($this->isEnabled() != true) {
                                $this->_matched_page = false;
                                return false;
                        }

			if (Mage::app()->getFrontController()->getRequest()->isAjax()) {
				$this->_matched_page = false;
                                return false;
			}

			$this->_ttl = $this->getConfigData('cache_ttl', 'cache_control');

			$module = Mage::app()->getFrontController()->getRequest()->getModuleName();
                        $controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
			$action = Mage::app()->getFrontController()->getRequest()->getActionName();


			if ($module == 'catalog' && $controller == 'category') {
				if ($this->getConfigData('zoom_category', 'cache_control')) {
					$this->_matched_page = true;
					$this->_ttl = $this->getConfigData('cache_category_ttl', 'cache_control');
				} else $this->_matched_page = false;
			} else if ($module == 'catalog' && $controller == 'product' && $action == 'view') {
				if ($this->getConfigData('zoom_product', 'cache_control')) {
					$this->_matched_page = true;
					$this->_ttl = $this->getConfigData('cache_product_ttl', 'cache_control');
				} else $this->_matched_page = false;
			} else if ($module == 'review' && $controller == 'product' && $action == 'list') {
				if ($this->getConfigData('zoom_review', 'cache_control')) {
					$this->_matched_page = true;
					$this->_ttl = $this->getConfigData('cache_review_ttl', 'cache_control');
				} else $this->_matched_page = false;
			} else if ($module == 'cms' && $controller == 'index' && ($action == 'noRoute' || $action == 'defaultNoRoute'))	{			
				$this->_matched_page = false;
				return false;
			} else if ($module == 'cms' && ($controller == 'page' || $controller == 'index')) {
				if ($this->getConfigData('zoom_cms', 'cache_control')) {
					$this->_matched_page = true;
					$this->_ttl = $this->getConfigData('cache_cms_ttl', 'cache_control');
				} else $this->_matched_page = false;
			} else
				$this->_matched_page = false;

			if ($this->_matched_page == false) {
				if ($this->regexMatchSimple($this->getConfigData('zoom_include_modules', 'cache_control'), "{$module}_{$controller}_{$action}"))
					$this->_matched_page = true;
			}

			if ($this->_matched_page == false) {
				if ($this->regexMatchSimple($this->getConfigData('zoom_include_uri', 'cache_control'), $this->getVarFromEzoomHandler('REQUEST')))
					$this->_matched_page = true;
			}
		
			if ($this->_matched_page == true) {
				if ($this->regexMatchSimple($this->getConfigData('zoom_exclude_modules', 'cache_control'), "{$module}_{$controller}_{$action}"))
					$this->_matched_page = false;
			}

			if ($this->_matched_page == true) {
				if ($this->regexMatchSimple($this->getConfigData('zoom_exclude_uri', 'cache_control'), $this->getVarFromEzoomHandler('REQUEST')))
					$this->_matched_page = false;
			}

			if ($this->_matched_page == true) {

				Mage::getSingleton('customer/session')->setLastEzzoomUrl(Mage::helper('core/url')->getCurrentUrl());

				$package_check  = Mage::getStoreConfig("design/package/ua_regexp");
				$template_check = Mage::getStoreConfig("design/theme/template_ua_regexp");
				$theme_check    = Mage::getStoreConfig("design/theme/default_ua_regexp");

				$this->_rewrite_package  = $this->regexMatchUserAgent($package_check);
				$this->_rewrite_template = $this->regexMatchUserAgent($template_check);
				$this->_rewrite_theme    = $this->regexMatchUserAgent($theme_check);
			}			

		} 

		return $this->_matched_page;

	}

	public function getCacheMatch () {
		return $this->_cache_match;
	}

	public function setCacheMatch($key) {
		$this->_cache_match = $key;

	}

	public function isEnabled() {

		return $this->getConfigData('active');

	}

	public function punchStatus($key) 
	{	

		if ($this->isEnabled()) {
			if ($this->matchedPage() || ($key == 'currency'))
				return $this->getConfigData('punch_' . $key, 'holepunch');
		} else
			return false;
	}

	public function regexMatchUserAgent($regex) {
		
		if (!$regex)
			return '';

        	$rules = @unserialize($regex);

        	if (empty($rules))
        	    return '';

	        foreach ($rules as $rule) {

	            $regexp = '/' . trim($rule['regexp'], '/') . '/';
	            if (@preg_match($regexp, $this->getVarFromEzoomHandler('AGENT')))
	                return $rule['value'];

	        }

		return '';

	}

	public function regexMatchSimple($regex, $matchTerm) {
		
		if (!$regex)
			return false;

        	$rules = @unserialize($regex);

        	if (empty($rules))
        	    return false;

	        foreach ($rules as $rule) {

	            $regexp = '#' . trim($rule['regexp'], '#') . '#';
	            if (@preg_match($regexp, $matchTerm))
	                return true;

	        }

		return false;

	}

	public function saveFile($file_name, $data, $straight_save = false) {

		$root = $this->getVarFromEzoomHandler('ZOOM_ROOT');

		if ($straight_save != false) {

			$this->_file_name = $this->createPathToFile($root . DS . $file_name, array(), false);

                        file_put_contents($this->_file_name, $data);

                        return $this->_file_name;

		}


 		$base = $root . DS . $this->getVarFromEzoomHandler('STORE');

		if ($this->_rewrite_package != '' || $this->_rewrite_template != '' || $this->_rewrite_theme != '') {
			
			if ($this->_rewrite_package != '')
				$base .= DS . $this->_rewrite_package;
			else
				$base .= DS . 'default';	

			if ($this->_rewrite_template != '')
				$base .= DS . $this->_rewrite_template;
			else
				$base .= DS . 'default';	

			if ($this->_rewrite_theme != '')
				$base .= DS . $this->_rewrite_theme;
			else
				$base .= DS . 'default';	

		}	
		
		$file_name = $base . $file_name;
	
		$symlink = null;
	
		if ($this->_tool_bar_finder && ((is_object(Mage::getSingleton('core/layout')->getBlock("product_list_toolbar"))
                    || Mage::registry('current_category')) && !Mage::registry('current_product'))) {
	
                        $attributes = array();
			$base_page  = true;
			$params = array();

			$state = $this->getToolbarState();
			
			$request = Mage::app()->getFrontController()->getRequest();


			$params = array_merge($state['page'], $state['control']);			

			foreach ($state['control'] as $key => $value)
				if ($value != $state['default'][$key]) {
					$base_page  = false;
					$break;
				}

			foreach ($state['filters'] as $key => $value)
				if ($request->getParam($key) != '') 
					$attributes[$key] = urlencode($request->getParam($key));	
		
			if (count($attributes) > 0) {
				$base_page  = false;
				$params = array_merge($params, $attributes);
			}

			

			if ($base_page == true)
				$symlink = $this->createPathToFile($file_name . DS . $this->getVarFromEzoomHandler('GET_POSTFIX'), $state['page']);
			else $params = array_merge($state['page'], $params);

			$file_name = $this->createPathToFile($file_name . DS . $this->getVarFromEzoomHandler('GET_POSTFIX'), $params); 

		} else { 
			$file_name = $this->createPathToFile($file_name, array(), false);
		}
		$this->_file_name = $file_name;



		if (!file_exists($file_name)) {

			$page = Mage::getModel('ezzoom/page')
				->setStoreId(Mage::app()->getStore()->getId())
				->setStoreCode(Mage::app()->getStore()->getCode())
				->setUri($this->getVarFromEzoomHandler('base_uri'))
				->setExpires(($this->_ttl > 0 ? Mage::getSingleton('core/date')->date(null, time()+$this->_ttl) : NULL))
				->setFilename($file_name)
				->setServer($this->getVarFromEzoomHandler('SERVER'))
				->setIsSecure(Mage::app()->getStore()->isCurrentlySecure())
				->save();

			$page_id = $page->getId();

			$data = str_replace($this->getVarFromEzoomHandler('TAG_PAGE'), $page_id, $data);
			$data = str_replace($this->getVarFromEzoomHandler('TAG_FILE'), Mage::helper('ezzoom')->compress($page->getFilename()), $data);

		} else { $page_id = 0; return 0; }

		if ($this->getConfigData('gzip') > 0) {

			if ($this->getConfigData('gzip_copy') == 1) {
				file_put_contents($file_name . ".gz", gzencode($data, $this->getConfigData('gzip')));
				file_put_contents($file_name, $data);
				if ($symlink) {
					if (!file_exists($symlink . ".gz"))
						symlink($file_name . ".gz", $symlink . ".gz");
					if (!file_exists($symlink))
						symlink($file_name, $symlink);
				}
			} else {
				file_put_contents($file_name, gzencode($data, $this->getConfigData('gzip')));
				if ($symlink)
					if (!file_exists($symlink))
						symlink($file_name, $symlink);
			}

		} else {
			
			file_put_contents($file_name, $data);
			if ($symlink)
				if (!file_exists($symlink))
                                        symlink($file_name, $symlink);

			
		}

		return $page_id;

	}

	public function deleteFile($filename) {
		if (file_exists($filename))
			unlink($filename);

		if (file_exists($filename . ".gz"))
			unlink($filename . ".gz");
		

	}

	public function createPathToFile($file_name, $params = array(), $add_index = true) {

			if (is_array($params))
                        	foreach ($params as $key => $value)
                        	        $file_name .= DS . $key . DS . $value;

			if ($add_index == true)
	                       $file_name .= DS . $this->getVarFromEzoomHandler('ZOOM_INDEX');

			$file_name = str_replace(DS . DS, DS, $file_name); 
	
	        	$path = explode(DS, $file_name);

	                $real_file_name = array_pop($path);
			if ($real_file_name == '')
				$real_file_name = $this->getVarFromEzoomHandler('ZOOM_INDEX');

        	        $path_finished = Mage::getBaseDir();

			if ($path[0] == '')
				array_shift($path);

                	foreach ($path as $directory) {
                        	if (!file_exists($path_finished . DS . $directory)) {
                                	mkdir($path_finished . DS . $directory);
				}
                               	$path_finished .= DS . $directory;
        	        }

	                return str_replace(DS . DS, DS, $path_finished . DS . $real_file_name);

	}		
}
