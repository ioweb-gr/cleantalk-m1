<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Api_CheckMessage extends Cleantalk_Antispam_Model_Api_AbstractGetApiRequest
{
    const REQUIRED_PARAMS = [
        'sender_email',
        'sender_ip',
        'js_on',
        'submit_time',
        'sender_nickname',
        'message',
    ];
    const METHOD_NAME = 'check_message';
    protected string $sender_email;
    protected string $sender_ip;
    protected string $js_on;
    protected string $submit_time;
    protected string $sender_nickname;
    protected string $message;
    protected string $sender_info;
    protected string $all_headers;

    public function _construct()
    {
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

    public function getSenderNickname(): string
    {
        return $this->sender_nickname;
    }

    public function setSenderNickname(string $sender_nickname): void
    {
        $this->params['sender_nickname'] = $sender_nickname;
        $this->sender_nickname = $sender_nickname;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->params['message'] = $message;
        $this->message = $message;
    }

    public function getSenderInfo(): string
    {
        return $this->sender_info;
    }

    public function getAllHeaders(): string
    {
        return $this->all_headers;
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

    public function getMethod(): string
    {
        return Zend_Http_Client::POST;
    }
}