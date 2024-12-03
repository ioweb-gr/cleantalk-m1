<?php
/*
 * Copyright (c) 2024. IOWEB TECHNOLOGIES
 */

class Cleantalk_Antispam_Model_Logger extends Mage_Core_Model_Abstract
{
    const LOG_FILE = 'cleantalk.log';

    public function log($message, $level = null)
    {
        Mage::log($message, $level, self::LOG_FILE);
    }
}