<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Client extends Mage_Core_Model_Abstract
{
    const API_URL = 'https://api.cleantalk.org';
    const API_URL_MODERATE_2 = 'https://moderate.cleantalk.org/api2.0';
    protected string $apiKey = '';
    /** @var Cleantalk_Antispam_Model_Logger|false|null */
    protected $logger;

    public function _construct()
    {
        $this->apiKey = (string)Mage::getStoreConfig('general/cleantalk/api_key');
        $this->logger = Mage::getSingleton('antispam/logger');
    }

    public function doGetRequest($methodName, $params = array(), $apiUri = 'https://api.cleantalk.org'): array
    {
        $client = $this->getClient();
        if (!empty($apiUri)) {
            $client->setUri($apiUri);
        }
        $client->setParameterGet('method_name', $methodName);
        $client->setParameterGet('auth_key', $this->apiKey);
        foreach ($params as $key => $value) {
            $client->setParameterGet($key, $value);
        }
        $this->logger->log(sprintf("Cleantalk API request: %s with params: %s", $methodName, json_encode($params)));
        try {
            $response = $client->request(Zend_Http_Client::GET);
            $this->logger->log(sprintf("Cleantalk API response: %s", $response->getBody()));
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'cleantalk.log');
            throw new Exception('Cleantalk API request failed');
        }
    }

    /**
     * @return Zend_Http_Client
     */
    private function getClient()
    {
        $client = new Zend_Http_Client();
        $client->setMethod(Zend_Http_Client::POST);
        $client->setUri(self::API_URL);
        $client->setConfig(array(
                               'maxredirects' => 0,
                               'timeout' => 5,
                               'keepalive' => true,
                               'adapter' => Zend_Http_Client_Adapter_Curl::class
                           ));
        return $client;
    }

    public function doJsonPostRequest($methodName, $params = array(), $apiUri = 'https://api.cleantalk.org'): array
    {
        $client = $this->getClient();
        $client->setMethod(Zend_Http_Client::POST);

        if (!empty($apiUri)) {
            $client->setUri($apiUri);
        }

        // Prepare the JSON payload
        $payload = array_merge(
            ['method_name' => $methodName, 'auth_key' => $this->apiKey],
            $params
        );
        $jsonPayload = json_encode($payload);

        // Set headers for JSON
        $client->setHeaders([
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json'
                            ]);

        // Set the raw data as the JSON payload
        $client->setRawData($jsonPayload, 'application/json');

        $this->logger->log(sprintf("Cleantalk API JSON request: %s with params: %s", $methodName, json_encode($params)));
        try {
            $response = $client->request(Zend_Http_Client::POST);
            $this->logger->log(sprintf("Cleantalk API JSON response: %s", $response->getBody()));
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'cleantalk.log');
            throw new Exception('Cleantalk API request failed');
        }
    }

    public function doPostRequest($methodName, $params = array(), $apiUri = 'https://api.cleantalk.org'): array
    {
        $client = $this->getClient();
        $client->setMethod(Zend_Http_Client::POST);
        if (!empty($apiUri)) {
            $client->setUri($apiUri);
        }
        $client->setParameterPost('method_name', $methodName);
        $client->setParameterPost('auth_key', $this->apiKey);
        foreach ($params as $key => $value) {
            $client->setParameterPost($key, $value);
        }
        $this->logger->log(sprintf("Cleantalk API POST request: %s with params: %s", $methodName, json_encode($params)));
        try {
            $response = $client->request(Zend_Http_Client::POST);
            $this->logger->log(sprintf("Cleantalk API POST response: %s", $response->getBody()));
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'cleantalk.log');
            throw new Exception('Cleantalk API request failed');
        }
    }
}