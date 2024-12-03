<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Api_CheckNewUser extends Cleantalk_Antispam_Model_Api_AbstractGetApiRequest
{
    const REQUIRED_PARAMS = [
        'method_name',
        'sender_email',
        'sender_ip',
        'js_on',
        'submit_time',
        'sender_nickname',
    ];
    const METHOD_NAME = 'check_newuser';
    const API_ENDPOINT_URL = 'https://moderate.cleantalk.org/api2.0';

    protected string $sender_email;
    protected string $sender_ip;
    protected string $js_on;
    protected string $submit_time;
    protected string $all_headers;
    protected string $sender_nickname;
    protected string $sender_info;
    protected string $response_lang;
    protected string $tz;
    protected string $phone;

    public function _construct()
    {
        parent::_construct();
        $this->params['method_name'] = self::METHOD_NAME;
        $this->sender_info = $this->buildSenderInfo();
        $this->all_headers = $this->buildAllHeaders();
    }

    public function getSenderEmail(): string
    {
        return $this->sender_email;
    }

    public function setSenderEmail(string $sender_email): void
    {
        $this->params['sender_email'] = $sender_email;
        $this->sender_email = $sender_email;
    }

    public function getSenderIp(): string
    {
        return $this->sender_ip;
    }

    public function setSenderIp(string $sender_ip): void
    {
        $this->params['sender_ip'] = $sender_ip;
        $this->sender_ip = $sender_ip;
    }

    public function getJsOn(): string
    {
        return $this->js_on;
    }

    public function setJsOn(string $js_on): void
    {
        $this->params['js_on'] = $js_on;
        $this->js_on = $js_on;
    }

    public function getSubmitTime(): string
    {
        return $this->submit_time;
    }

    public function setSubmitTime(string $submit_time): void
    {
        $this->params['submit_time'] = $submit_time;
        $this->submit_time = $submit_time;
    }

    public function getAllHeaders(): string
    {
        return $this->all_headers;
    }

    public function setAllHeaders(string $all_headers): void
    {
        $this->params['all_headers'] = $all_headers;
        $this->all_headers = $all_headers;
    }

    public function getSenderNickname(): string
    {
        return $this->sender_nickname;
    }

    public function setSenderNickname(string $sender_nickname): void
    {
        $this->params['sender_nickname'] = $sender_nickname;
        $this->sender_nickname = $sender_nickname;
    }

    public function getSenderInfo(): string
    {
        return $this->sender_info;
    }

    public function setSenderInfo(string $sender_info): void
    {
        $this->params['sender_info'] = $sender_info;
        $this->sender_info = $sender_info;
    }

    public function getResponseLang(): string
    {
        return $this->response_lang;
    }

    public function setResponseLang(string $response_lang): void
    {
        $this->params['response_lang'] = $response_lang;
        $this->response_lang = $response_lang;
    }

    public function getTz(): string
    {
        return $this->tz;
    }

    public function setTz(string $tz): void
    {
        $this->params['tz'] = $tz;
        $this->tz = $tz;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->params['phone'] = $phone;
        $this->phone = $phone;
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
    }

    public function getApiUrl(): string
    {
        return Cleantalk_Antispam_Model_Client::API_URL_MODERATE_2;
    }

    public function getMethod(): string
    {
        return self::METHOD_JSON;
    }


}