<?php

/**
 * Zoom Page Cache Collection
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Model_Mysql4_Page_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize object
     *
     * @return void
     */
    public function _construct()
    {
	$this->_init('ezzoom/page');
    }

    public function getStats() {

        $this->getSelect()->columns('SUM(hits) AS hits')->columns('COUNT(*) AS misses');

	return $this->getFirstItem();
    }

    public function consolidateMisses() {

	$this->getSelect()->columns('SUM(hits) AS hits')->group(array('store_id', 'uri'));
	return $this;

    }

    public function filterByStore ($store_id) {

	if ($store_id > 0)
        	$this->addFieldToFilter('store_id', array('eq' => $store_id));

        return $this;

    }

    public function delete() {

	foreach ($this as $item) {
		Mage::helper('ezzoom')->deleteFile($item->getFilename());
		$item->delete();
	}

	return true;

    }

    public function getSize() {

        if(is_null($this->_totalRecords))
        {
            $this->_renderFilters();
            $sql = $this->getSelect();
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $res = $read->query($sql->assemble())->fetchAll();
            $this->_totalRecords = count($res);
        }
        return intval($this->_totalRecords);

    }


}
