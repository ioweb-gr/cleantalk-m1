<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */
interface Cleantalk_Antispam_Model_Api_GetApiRequestInterface
{
    public function getMethodName(): string;

    public function getParams(): array;

    public function setParams(array $params): void;

    public function setParam($key, $value): void;

    public function addParam($key, $value): void;

    public function execute(): array;

    public function validateRequest(): void;

    public function getMethod(): string;

    public function getApiUrl(): string;
}