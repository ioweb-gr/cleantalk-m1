<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Cron_CheckUsersForSpamRegistrations extends Mage_Core_Model_Abstract
{
    const BATCH_SIZE = 100;
    protected Mage_Core_Model_Abstract|Cleantalk_Antispam_Model_Logger|false $logger;
    protected Mage_Core_Model_Abstract|false|Cleantalk_Antispam_Model_Api_CheckNewUser $checkNewUser;

    protected function _construct()
    {
        parent::_construct();
        $this->logger = Mage::getModel('antispam/logger');
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return void
     * @throws Exception
     * @deprecated in favor of processCustomerWithSpamCheckCms
     */
    private function processCustomerWithCheckNewUser(Mage_Customer_Model_Customer $customer)
    {
        $this->logger->log(sprintf("Checking if customer %s - %s is spam", $customer->getId(), $customer->getEmail()));
        /** @var Cleantalk_Antispam_Model_Api_CheckNewUser $checkNewUser */
        $checkNewUser = Mage::getModel('antispam/api_checkNewUser');
        $checkNewUser->setJsOn(1);
        $checkNewUser->setSenderEmail($customer->getEmail());
        $checkNewUser->setPhone($this->getCustomerPhone($customer));
        $checkNewUser->setTz($this->getTimezoneOffset());
        $checkNewUser->setResponseLang($this->getCustomerLanguage($customer));
        $checkNewUser->setSenderNickname($customer->getFirstname() . ' ' . $customer->getLastname());
        $checkNewUser->setSenderIp('');
        $checkNewUser->setSubmitTime('');
        $result = $checkNewUser->execute();
        if (isset($result['allow']) && (bool)$result['allow']) {
            $this->logger->log(sprintf("Customer %s - %s is not spam: %s", $customer->getId(), $customer->getEmail(),
                                       json_encode($result)));
            $customer->setData('is_cleantalk_spam_user', 0);
            $customer->getResource()->saveAttribute($customer, 'is_cleantalk_spam_user');
        } else {
            $this->logger->log(sprintf("Customer %s - %s is spam: %s", $customer->getId(), $customer->getEmail(),
                                       json_encode($result)));
            $customer->setData('is_cleantalk_spam_user', 1);
            $customer->getResource()->saveAttribute($customer, 'is_cleantalk_spam_user');
        }

        return;
    }

    private function getCustomerPhone(Mage_Customer_Model_Customer $customer)
    {
        $billingAddress = $customer->getDefaultBillingAddress();
        if ($billingAddress) {
            return $billingAddress->getTelephone();
        }
        return '';
    }

    private function getTimezoneOffset()
    {
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $dateTimeZone = new DateTimeZone($timezone);
        $dateTime = new DateTime('now', $dateTimeZone);
        return $dateTime->getOffset();
    }

    private function getCustomerLanguage(Mage_Customer_Model_Customer $customer)
    {
        $locale = Mage::getStoreConfig('general/locale/code', $customer->getStoreId());
        return substr($locale, 0, 2);
    }

    public function execute()
    {
        $customers = $this->getUncheckedCustomers();
        foreach ($customers as $customer) {
            $this->processCustomerWithSpamCheckCms($customer);

        }
    }

    private function getUncheckedCustomers()
    {
        $customerCollection = Mage::getModel('customer/customer')->getCollection();
        $customerCollection->addAttributeToSelect('*');
        /** @var Mage_Eav_Model_Resource_Entity_Attribute $attribute */
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'is_cleantalk_spam_user');

        // Manually join the attribute table with a LEFT JOIN
        $customerCollection->getSelect()->joinLeft(
            ['cleantalk_attr' => $attribute->getBackendTable()],
            'e.entity_id = cleantalk_attr.entity_id AND cleantalk_attr.attribute_id = ' . (int)$attribute->getId(),
            ['is_cleantalk_spam_user' => 'cleantalk_attr.value']
        );

        // Add the null condition filter
        $customerCollection->getSelect()->where('cleantalk_attr.value IS NULL');

        // Set page size and current page
        $customerCollection->setPageSize(self::BATCH_SIZE);
        $customerCollection->setCurPage(1);

        return $customerCollection->getItems();
    }

    private function processCustomerWithSpamCheckCms(Mage_Customer_Model_Customer $customer)
    {
        $this->logger->log(sprintf("Checking if customer %s - %s is spam", $customer->getId(), $customer->getEmail()));
        /** @var Cleantalk_Antispam_Model_Api_SpamCheckCms $spamCheckCmsRequest */
        $spamCheckCmsRequest = Mage::getModel('antispam/api_spamCheckCms');
        $email = $customer->getEmail();
        $spamCheckCmsRequest->setEmail($email);
        $result = $spamCheckCmsRequest->execute();
        if (isset($result["data"][$email]["appears"]) && (bool)$result["data"][$email]["appears"]) {
            $this->logger->log(sprintf("Customer %s - %s is spam: %s", $customer->getId(), $customer->getEmail(),
                                       json_encode($result)));
            $customer->setData('is_cleantalk_spam_user', 1);
            $customer->getResource()->saveAttribute($customer, 'is_cleantalk_spam_user');
        } else {
            $this->logger->log(sprintf("Customer %s - %s is not spam: %s", $customer->getId(), $customer->getEmail(),
                                       json_encode($result)));
            $customer->setData('is_cleantalk_spam_user', 0);
            $customer->getResource()->saveAttribute($customer, 'is_cleantalk_spam_user');
        }
    }
}