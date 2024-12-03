<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Helper_DataPatch extends Mage_Core_Helper_Abstract
{

    public function addIsCleantalkSpamUserCustomerAttribute($installer)
    {
        $installer = new Mage_Eav_Model_Entity_Setup('core_setup');
        /* @var $installer Mage_Eav_Model_Entity_Setup */

        $installer->startSetup();

// Check if the attribute already exists
        $attributeCode = 'is_cleantalk_spam_user';
        $entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
        $attribute = Mage::getSingleton('eav/config')->getAttribute($entityTypeId, $attributeCode);

        if (!$attribute || !$attribute->getId()) {
            // Add the new attribute
            $installer->addAttribute('customer', $attributeCode, [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Cleantalk Spam User',
                'input' => 'select',
                'class' => '',
                'source' => 'eav/entity_attribute_source_boolean',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'note' => ''
            ]);

            // Make the attribute available in forms
            $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
            $attribute->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);
            $attribute->save();
        }

        $installer->endSetup();
    }

}