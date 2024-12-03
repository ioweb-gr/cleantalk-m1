<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Api_GetApiKey extends Cleantalk_Antispam_Model_Api_AbstractGetApiRequest
{
    const REQUIRED_PARAMS = [
        'email',
        'website',
    ];
    const METHOD_NAME = 'get_api_key';
    protected string $email;
    protected string $website;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function validateRequest(): void
    {
        $params = $this->getParams();
        foreach (self::REQUIRED_PARAMS as $param) {
            if (!isset($params[$param])) {
                throw new Exception('Parameter ' . $param . ' is required');
            }
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->params['email'] = $email;
        $this->email = $email;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): void
    {
        $this->params['website'] = $website;
        $this->website = $website;
    }

    public function getMethod(): string
    {
        return Zend_Http_Client::POST;
    }
}