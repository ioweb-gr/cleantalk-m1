<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */


abstract class Cleantalk_Antispam_Model_Api_AbstractGetApiRequest extends \Mage_Core_Model_Abstract implements Cleantalk_Antispam_Model_Api_GetApiRequestInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_JSON = 'JSON';
    protected Cleantalk_Antispam_Model_Client $client;
    protected array $params = array();

    public function _construct()
    {
        parent::_construct();
        $this->client = Mage::getModel('antispam/client');
    }

    public function setParam($key, $value): void
    {
        $this->params[$key] = $value;
    }

    public function execute(): array
    {
        $this->validateRequest();


        if ($this->getMethod() === self::METHOD_GET) {
            return $this->client->doGetRequest($this->getMethodName(), $this->getParams(), $this->getApiUrl());
        }
        if ($this->getMethod() === self::METHOD_POST) {
            return $this->client->doPostRequest($this->getMethodName(), $this->getParams(), $this->getApiUrl());
        }
        if ($this->getMethod() === self::METHOD_JSON) {
            return $this->client->doJsonPostRequest($this->getMethodName(), $this->getParams(), $this->getApiUrl());
        }
        throw new Exception('Method not allowed');
    }

    public function getMethod(): string
    {
        return Zend_Http_Client::POST;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getApiUrl(): string
    {
        return Cleantalk_Antispam_Model_Client::API_URL;
    }

    public function addParam($key, $value): void
    {
        $this->params[$key] = $value;
    }

    protected function buildAllHeaders(): string
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            // Check for HTTP headers
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }

        // Additional headers that are not prefixed with HTTP_
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }

        return json_encode($headers);
    }

    protected function buildSenderInfo(): string
    {
        // Build sender info based on request and server variables
        return json_encode([
                               'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                               'REFFERRER' => $_SERVER['HTTP_REFERER'] ?? '',
                           ]);
    }


}