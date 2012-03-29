<?php

/**
 * Zoom Page Cache Object
 *
 * @category   Ezapps
 * @package    Ezapps_Zoom
 * @author     Ezra Morse (http://www.ezapps.ca)
 * @license:   EPL 1.0
 */

class Ezapps_Zoom_Model_System_Config_Backend_Gzip extends Mage_Core_Model_Config_Data
{

    public function toOptionArray()
    {
        $result = array();
        $result[] = array(
                'label' => "Off",
                'value' => "0"
            );
        $result[] = array(
                'label' => "1",
                'value' => "1"
            );
        $result[] = array(
                'label' => "2",
                'value' => "2"
            );
        $result[] = array(
                'label' => "3",
                'value' => "3"
            );
        $result[] = array(
                'label' => "4",
                'value' => "4"
            );
        $result[] = array(
                'label' => "5",
                'value' => "5"
            );
        $result[] = array(
                'label' => "6",
                'value' => "6"
            );
        $result[] = array(
                'label' => "7",
                'value' => "7"
            );
        $result[] = array(
                'label' => "8",
                'value' => "8"
            );
        $result[] = array(
                'label' => "9",
                'value' => "9"
            );

        return $result;
    }


    public function afterCommitCallback()
    {

        $data = Mage::helper('ezzoom')->getRewrites();

        foreach (Mage::getModel('core/store')->getCollection() as $store)
                        $stores[] = $store->getId();

        foreach ($stores as $store_id) {

                        $path = Mage::getBaseDir() . DS . Mage::helper('ezzoom')->getVarFromEzoomHandler('ZOOM_ROOT');

                        $file1 = $path . DS . Mage::helper('ezzoom')->getVarFromEzoomHandler('STORE') . DS .
                                 Mage::helper('ezzoom')->getVarFromEzoomHandler('ZOOM_CLIENT_MATCH_DATA');

                        if (file_exists($file1))
                                unlink($file1);

                        if (function_exists('apc_delete')) {
                                $key1 = Mage::helper('ezzoom')->getVarFromEzoomHandler('APC_KEY') . DS .
                                        Mage::helper('ezzoom')->getVarFromEzoomHandler('STORE') . DS .
                                        Mage::helper('ezzoom')->getVarFromEzoomHandler('ZOOM_CLIENT_MATCH_DATA');
                                if (apc_exists($key1))
                                        apc_delete($key1);
                        }

        }


        return parent::afterCommitCallback();

    }


}
