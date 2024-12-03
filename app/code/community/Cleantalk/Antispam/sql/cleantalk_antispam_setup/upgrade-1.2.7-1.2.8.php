<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Check if the attribute already exists
/** @var Cleantalk_Antispam_Helper_DataPatch $dataPatchHelper */
$dataPatchHelper = Mage::helper('antispam/dataPatch');
$dataPatchHelper->addIsCleantalkSpamUserCustomerAttribute($installer);

$installer->endSetup();
