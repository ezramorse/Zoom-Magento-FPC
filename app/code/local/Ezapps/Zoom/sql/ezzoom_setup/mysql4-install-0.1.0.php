<?php
/**
 * Zoom 0.1.0 Installer
 *
 * @author      Ezra Morse (http://www.ezapps.ca)
 * @license:    EPL 1.0
 */


$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('ezapps_zoom_page')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server` varchar(128) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `store_id` int(11) NOT NULL,
  `store_code` varchar(255) NOT NULL,
  `expires` datetime DEFAULT NULL,
  `hits` int(11) NOT NULL,
  `filename` text NOT NULL,
  `ignore_entry` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `URL` (`uri`,`store_id`),
  KEY `FILE` (`filename`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ezapps Zoom Cache Data';


");

$installer->endSetup();

?>
