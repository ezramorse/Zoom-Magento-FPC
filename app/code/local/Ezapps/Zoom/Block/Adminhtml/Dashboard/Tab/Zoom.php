<?php

/**
 * Adminhtml dashboard diagram tabs
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Block_Adminhtml_Dashboard_Tab_Zoom extends Mage_Adminhtml_Block_Dashboard_Graph
{
    /**
     * Initialize object
     *
     * @return void
     */
    public function __construct()
    {
        $this->setHtmlId('zoom');
       	parent::__construct();
    }

    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepareData()
    {
	$this->setTemplate('ezzoom/dashboard/graph.phtml');
        $this->setDataHelperName('adminhtml/dashboard_order');
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->getDataHelper()->setParam('website', $this->getRequest()->getParam('website'));
        $this->getDataHelper()->setParam('group', $this->getRequest()->getParam('group'));

	$pageDataMisses = Mage::getModel('ezzoom/page')->getCollection()->addFieldToFilter('ignore_entry', array('neq' => 1))->filterByStore($this->getDataHelper()->getParam('store'))->getStats();
	$pageDataHits = Mage::getModel('ezzoom/page')->getCollection()->filterByStore($this->getDataHelper()->getParam('store'))->getStats();

	if ($pageDataHits->getHits() + $pageDataMisses->getMisses() > 0) {
		$hitp = number_format(($pageDataHits->getHits()/($pageDataHits->getHits()+$pageDataMisses->getMisses()))*100) . "%"; 
		$misp = number_format(($pageDataMisses->getMisses()/($pageDataHits->getHits()+$pageDataMisses->getMisses()))*100) . "%";
	} else {
		$hitp = "0.00%";	
		$misp = "0.00%";
	}

	$this->setStatData(array('hits' => $pageDataHits->getHits(), 'misses'=> $pageDataMisses->getMisses(), 'hitp'=> $hitp, 'misp' => $misp));

    }

    /**
     * Get tab template
     *
     * @return string
     */
    protected function _getTabTemplate()
    {
        return 'ezzoom/dashboard/graph.phtml';
    }

    /**
     * Get chart url
     *
     * @param bool $directUrl
     * @return string
     */
    public function getChartUrl($directUrl = true)
    {  
        $params = array(
            'cht'  => 'p3',
            'chf'  => 'bg,lg,90,ffffff,0.1,ededed,0',
            'chm'  => 'B,9ac1d9,0,0,0',
            'chco' => '3a8ab8'
        );


        //Google encoding values
        if ($this->_encoding == "s") {
            // simple encoding
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "_";
        } else {
            // extended encoding
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "__";
        }

        // chart size
        $params['chs'] = $this->getWidth().'x'.$this->getHeight();


	$pageData = $this->getStatData();
	$params['chd']  = "t:" . (int) $pageData['hits']  . "," . (int) $pageData['misses'];
	$params['chds'] = "0," . ($pageData['misses'] > $pageData['hits'] ? $pageData['misses'] : $pageData['hits']);
	//$params['chl']  = "Hits|Misses";	
	$params['chdl']  = "Hits|Misses";
	$params['chdlp']= "r|l";
	$params['chma'] = "5,5,5,5|85,100";

        // return the encoded data
        if ($directUrl) {
            $p = array();
            foreach ($params as $name => $value) {
                $p[] = $name . '=' .urlencode($value);
            }
            return self::API_URL . '?' . implode('&', $p);
        } else {
            $gaData = urlencode(base64_encode(serialize($params)));
            $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
            $params = array('ga' => $gaData, 'h' => $gaHash);
            return $this->getUrl('*/*/tunnel', array('_query' => $params));
        }
    }


    public function getCount()
    {
	$pageData = $this->getStatData();
	return ($pageData['misses'] > 0);

    }

}
