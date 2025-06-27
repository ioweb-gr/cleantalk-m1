<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Api_SpamCheckCms extends Cleantalk_Antispam_Model_Api_AbstractGetApiRequest
{
    const REQUIRED_PARAMS = [
    ];
    const METHOD_NAME = 'spam_check_cms';

    protected string $email;
    protected string $ip;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->params['email'] = $email;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
        $this->params['ip'] = $ip;
    }

    public function _construct()
    {
        parent::_construct();
        $this->params['method_name'] = self::METHOD_NAME;
    }

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
        if (empty($this->email) && empty($this->ip)) {
            throw new Exception('Parameter email or ip is required');
        }
    }

    public function getApiUrl(): string
    {
        return Cleantalk_Antispam_Model_Client::API_URL;
    }

    public function getMethod(): string
    {
        return self::METHOD_GET;
    }


}