<?php 
/**
 * EZAPPS Zoom Handler
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Handler
{

	private static $instance;
	private static $ZOOM_ROOT;               
	private static $SERVER;			
	private static $REQUEST;		
	private static $AGENT;
	private static $GET_POSTFIX;
        private static $base_uri;  	
	private static $_start; 

	const		DS                      = DIRECTORY_SEPARATOR;
	
	private static $ZOOM_INDEX              = 'ZOOM_INDEX';
	private static $GET_STEM		= 'zoom_';
	private static $APC_KEY			= 'ZOOM';	
	private static $ZOOM_CLIENT_DATA	= 'ZOOM_CLIENT_MATCH_DATA';
	private static $HOLE_START		= '<span id="hole-';
        private static $HOLE_END_PRE            = '-pre"></span>';
        private static $HOLE_END_POST           = '-post"></span>';
        private static $TAG_START               = '<span id="ezzoom-ajax-footer-pre"></span>';
        private static $TAG_END                 = '<span id="ezzoom-ajax-footer-post"></span>';
        private static $TAG_START_CURRENCY      = '<span id="ezzoom-ajax-footer-currency-pre"></span>';
        private static $TAG_END_CURRENCY        = '<span id="ezzoom-ajax-footer-currency-post"></span>';
        private static $TAG_PAGE                = '{{ezzoom-page-id}}';
        private static $TAG_FILE                = '{{ezzoom-file}}';
	private static $GET_URI_MARK		= '/zget/';
	private static $STORE			= '';
        private static $DEFAULT_FILL            = array();

	private static $_no_client_data_found 	= false;

	function __construct() {
		
		self::$ZOOM_ROOT   = 'var' . DIRECTORY_SEPARATOR . 'zoom';
		self::$SERVER      = $_SERVER["SERVER_NAME"];
		self::$REQUEST     = $_SERVER["REQUEST_URI"];
		self::$AGENT       = (in_array("HTTP_USER_AGENT", $_SERVER) ? $_SERVER["HTTP_USER_AGENT"] : '');
		self::$_start      = microtime(true);
		self::$GET_POSTFIX = str_replace('/', self::DS, self::$GET_URI_MARK);
                self::$STORE       = (array_key_exists('store', $_COOKIE) ? $_COOKIE['store'] :
                                     (array_key_exists('MAGE_RUN_CODE', $_SERVER) ? $_SERVER['MAGE_RUN_CODE'] : ''));
		if (self::$STORE == '')
			self::$STORE = 'default';

		$url = parse_url(self::$REQUEST);
		$test = explode("/", $url['path']);
		if ($test[(count($test) - 1)] == "")
			self::$base_uri = str_replace('/', self::DS, $url['path'] . self::$ZOOM_INDEX);
		else if (!strstr($test[(count($test)-1)], '.'))
			self::$base_uri = str_replace('/', self::DS, $url['path'] . '/' . self::$ZOOM_INDEX);
		else
			self::$base_uri = str_replace('/', self::DS, $url['path']);



		// Customize for stores if default fill is desired
		/*self::$DEFAULT_FILL['default']['links'] = '<ul class="links">' .
                        '<li class="first"><a href="{{secure_url}}customer/account/" title="My Account">My Account</a></li>' .
                        '        <li><a href="{{secure_url}}wishlist/" title="My Wishlist">My Wishlist</a></li>' .
                        '        <li><a href="{{unsecure_url}}checkout/cart/" title="My Cart" class="top-link-cart">My Cart</a></li>' .
                        '        <li><a href="{{secure_url}}checkout/" title="Checkout" class="top-link-checkout">Checkout</a></li>' .
                        '        <li class=" last"><a href="{{secure_url}}customer/account/login/" title="Log In">Log In</a></li>' .
            		'</ul>';

		self::$DEFAULT_FILL['default']['cart'] = '<a href="{{unsecure_url}}checkout/cart/">0 items in your cart</a>';*/

		

	}

	public function getVars() {
		return array(
 			'ZOOM_CLIENT_MATCH_DATA'	=> self::$ZOOM_CLIENT_DATA,
 			'ZOOM_ROOT'			=> self::$ZOOM_ROOT,
			'SERVER'			=> self::$SERVER,
			'REQUEST'			=> self::$REQUEST,
			'AGENT'				=> self::$AGENT,
			'APC_KEY'			=> self::$APC_KEY,
			'base_uri'			=> self::$base_uri,
			'ZOOM_INDEX'			=> self::$ZOOM_INDEX,
			'HOLE_START'			=> self::$HOLE_START,
			'HOLE_END_PRE'			=> self::$HOLE_END_PRE,
			'HOLE_END_POST'			=> self::$HOLE_END_POST,
			'GET_STEM'			=> self::$GET_STEM,
			'TAG_START'			=> self::$TAG_START,
			'TAG_END'			=> self::$TAG_END,
			'TAG_START_CURRENCY'		=> self::$TAG_START_CURRENCY,
			'TAG_END_CURRENCY'		=> self::$TAG_END_CURRENCY,
			'TAG_PAGE'			=> self::$TAG_PAGE,
			'TAG_FILE'			=> self::$TAG_FILE,
			'GET_POSTFIX'			=> self::$GET_POSTFIX,
			'GET_URI_MARK'			=> self::$GET_URI_MARK,
			'STORE'				=> self::$STORE,
			'DEFAULT_FILL'                  => self::$DEFAULT_FILL,
			);
	}

	public static function getInstance() { 

    		if(!self::$instance) { 
		      self::$instance = new self(); 
		    } 

		return self::$instance; 
	} 

	public static function tryRetrieveCacheFile() {

		$test_for_rewrites = explode(self::$GET_URI_MARK, self::$base_uri);

		if (array_key_exists(1, $test_for_rewrites)) {
			self::$base_uri         = $test_for_rewrites[0];

			$_SERVER["REQUEST_URI"] = $test_for_rewrites[0];
			$args = explode("/", $test_for_rewrites[1]);
			for ($i = 0; $i < count($args); $i=$i+2)
				$_GET[$args[$i]] = $args[$i+1]; 
		}

		$client_matches = null;

	        $zoom_package   = '';
	        $zoom_template  = '';
	        $zoom_theme     = '';
		$apc_grab	= true;

		if (function_exists('apc_fetch')) {
			$apckey = self::$APC_KEY . self::DS . self::$STORE . self::DS . self::$ZOOM_CLIENT_DATA;
                	if (apc_exists($apckey))
				$client_matches = apc_fetch($apckey);

			if (is_null($client_matches))
                                        $apc_grab = false;
                }
 
		if (is_null($client_matches)) {

			$zoom_client_file = self::$ZOOM_ROOT . self::DS . self::$STORE . self::DS . self::$ZOOM_CLIENT_DATA;

			if (file_exists($zoom_client_file)) {

		        	$client_matches = json_decode(file_get_contents($zoom_client_file), true);
				if ($apc_grab != true)
					apc_add($apckey, $client_matches);
			}
		}
		
		if ($client_matches) {
		        $zoom_package  = self::checkClient($client_matches['package']);
		        $zoom_template = self::checkClient($client_matches['template']);
		        $zoom_theme    = self::checkClient($client_matches['theme']);

        		if ($zoom_package != '' || $zoom_template != '' || $zoom_theme != '') {
                		$file_to_check = self::$ZOOM_ROOT . self::DS .  self::$STORE . 
					     self::DS . ($zoom_package  != '' ? $zoom_package  : 'default') .
                                             self::DS . ($zoom_template != '' ? $zoom_template : 'default') .
                                             self::DS . ($zoom_theme    != '' ? $zoom_theme    : 'default') .
                                             self::$base_uri;
        		} else $file_to_check = self::$ZOOM_ROOT . self::DS .  self::$STORE . self::$base_uri;

		} else {

			self::$_no_client_data_found = true;
			return;
		}

		$params   = array();
		$files    = array();

		list($page_name) =  array_keys($client_matches['controls']['page']);	

		$p_track  = 0;

		foreach ($client_matches['controls']['control'] as $key => $value)
			if (array_key_exists($key, $_GET))
				$params[$key] = $_GET[$key];
			else if (array_key_exists(self::$GET_STEM . $key, $_COOKIE))
                                $params[$key] = $_COOKIE[self::$GET_STEM . $key];

		if (count($params) > 0) {
			foreach ($client_matches['controls']['filters'] as $key => $value)
				if (array_key_exists($key, $_GET))
					$params[$key] = urlencode($_GET[$key]);
			if (array_key_exists($page_name, $_GET)) {
				$page = array($page_name => $_GET[$page_name]);
				$files[] = self::paramsToFile(array_merge($page, $params), $file_to_check);
			} else $files[] = self::paramsToFile(array_merge(array($page_name => 1), $params), $file_to_check);
	
		} else if (array_key_exists($page_name, $_GET))
			$files[] = self::paramsToFile(array($page_name => $_GET[$page_name]), $file_to_check);
		else {
			$files[] = self::paramsToFile(array($page_name => 1), $file_to_check);
		}
	
		$page = false;			


		$files[] = $file_to_check; 

                if (preg_match('/MSIE [1-6]/i',self::$AGENT)) $gzip = false; else $gzip = true;
		foreach ($files as $attempt) {

			if ($gzip && file_exists($attempt . ".gz")) 
        			$page = file_get_contents($attempt . ".gz");
			else if (file_exists($attempt))
        			$page = file_get_contents($attempt);

			if ($page != false)
				break;
		}

		if ($page != false) {
			if (bin2hex(substr($page,0,2)) == '1f8b' ) {
				if(count(ob_list_handlers()) > 0)
					ob_end_clean();
				header("X-Compression: gzip");
				header("Content-Encoding: gzip");
			}

			echo $page;
			exit();
		}

		return;

	}

	private function paramsToFile($params, $uri) {

		if (count($params) > 0) {

			$uri .= self::$GET_POSTFIX;

                        foreach ($params as $key => $value) {
                                 $uri .= self::DS . $key;
                                 $uri .= self::DS . $value;
                        }

                        $uri .= self::DS . self::$ZOOM_INDEX;
			
                        return str_replace(self::DS . self::DS, self::DS, $uri);
			

		} else return array();

	}	

	public static function punchHoles($buffer) {

		if (class_exists('Mage')) {

	   		$modules = (array)Mage::getConfig()->getNode('modules')->children();
	        	if (array_key_exists('Ezapps_Zoom', $modules) && $modules['Ezapps_Zoom']->is('active') ) {

				if (self::$_no_client_data_found != false) {

					Mage::helper('ezzoom')->generateClientRewrites();

				} else if (Mage::helper('ezzoom')->matchedPage() == true) {

                                        $start  = self::$TAG_START_CURRENCY;
                                        $end    = self::$TAG_END_CURRENCY;
                                        $starta = self::$TAG_START;
                                        $enda   = self::$TAG_END;
                                        $search = array("#{$starta}#sU", "#{$enda}#sU", "#{$start}(.*){$end}#sU");
                                        $replace = array('', '', '');

                                        $punched_file = Mage::helper('ezzoom')->punchHoles(preg_replace($search, $replace, $buffer));

					$id = Mage::helper('ezzoom')->saveFile(self::$base_uri, $punched_file);
				}

				Mage::helper('ezzoom')->setRenderTime(microtime(true) - self::$_start);	

				$start = self::$TAG_START;
				$end   = self::$TAG_END;

				return preg_replace("#$start(.*)$end#sU", '', Mage::helper('ezzoom')->renderHeaderDebug($buffer));
			}
		}
		return $buffer;
		
	}
	
	public static function startBuffer() {
		ob_start(array(get_class(self::getInstance()), 'punchHoles'));
	}

	private static function checkClient($regex) {

		if (!$regex)
                	return '';

                $rules = @unserialize($regex);

                if (empty($rules))
                	return '';

                foreach ($rules as $rule) {
                    $regexp = '#' . trim($rule['regexp'], '#') . '#';
                    if (@preg_match($regexp, self::$AGENT))
                        return $rule['value'];

                }

	}

}

$zoom_controller = Ezapps_Zoom_Handler::getInstance();

if (!(array_key_exists('___store', $_GET) && array_key_exists('___from_store', $_GET))) {
	if (!(array_key_exists('isAjax', $_GET) || array_key_exists('ajax', $_GET) || array_key_exists('isAjax', $_POST) || array_key_exists('ajax', $_POST))) {

		$zoom_controller->tryRetrieveCacheFile();

		$zoom_controller->startBuffer();
	} 
}

?>
