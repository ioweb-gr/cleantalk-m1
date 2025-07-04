<?php

require_once(dirname(__FILE__) . '/../custom_config.php');

class Cleantalk_Antispam_Model_Api extends Mage_Core_Model_Abstract
{
    /**
     * @var Cleantalk_Antispam_Model_Logger|Mage_Core_Model_Abstract|null
     */
    private $logger;

    /**
     * Universal method for error message
     * @return error template
     */

    static function CleantalkDie($message)
    {
        $error_tpl = file_get_contents(dirname(__FILE__) . "/error.html");
        print str_replace('%ERROR_TEXT%', $message, $error_tpl);
        die();
    }

    /**
     * Universal method for page addon
     * Needed for correct JavaScript detection, for example.
     * @return string Template addon text
     */
    static function PageAddon()
    {

        $field_name = 'ct_checkjs'; // todo - move this to class constant
        $ct_check_def = '0';
        if (!isset($_COOKIE[$field_name])) setcookie($field_name, $ct_check_def, 0, '/');

        $ct_check_value = self::GetCheckJSValue();
        $js_template = '<script type="text/javascript">
// <![CDATA[
function ctSetCookie(c_name, value) {
 document.cookie = c_name + "=" + escape(value) + "; path=/";
}
ctSetCookie("%s", "%s");
// ]]>
</script>
';
        $ct_template_addon_body = sprintf($js_template, $field_name, $ct_check_value);
        return $ct_template_addon_body;
    }

    /**
     * CleanTalk inner function - JavaScript checking value, depends on system variables
     * @return string System depending md5 hash
     */
    static function GetCheckJSValue()
    {
        return md5(Mage::getStoreConfig('general/cleantalk/api_key') . '_' . Mage::getStoreConfig('trans_email/ident_general/email'));
    }

    /**
     * Universal method for checking comment or new user for spam
     * It makes checking itself
     * @param &array Entity to check (comment or new user)
     * @param boolean Notify admin about errors by email or not (default FALSE)
     * @return array|null Checking result or NULL when bad params
     */
    static function CheckSpam(&$arEntity, $bSendEmail = FALSE)
    {

        $logger = Mage::getSingleton('antispam/logger');
        // Exclusions

        // by URL
        // Don't send request if current url is in exclusions list
        $url_exclusion = CleantalkCustomConfig::get_url_exclusions();

        if ($url_exclusion) {
            foreach ($url_exclusion as $key => $value)
                if (strpos($_SERVER['REQUEST_URI'], $value) !== false)
                    return;
        }
        if (!is_array($arEntity) || !array_key_exists('type', $arEntity)) {
            return;
        }

        // by Type
        if ($arEntity['type'] != 'comment' && $arEntity['type'] != 'register') {
            return;
        }

        // by Data
        if (
            !empty($arEntity['message_body']) && is_array($arEntity['message_body']) && // Msg is array

            // File upload
            (
                isset($arEntity['message_body']['Filename'], $arEntity['message_body']['Upload']) &&
                $arEntity['message_body']['Upload'] === 'Submit Query'
            )
        ) {
            return;
        }

        $type = $arEntity['type'];

        $ct_key = Mage::getStoreConfig('general/cleantalk/api_key');
        $ct_ws = self::GetWorkServer();


        if (!isset($_COOKIE['ct_checkjs'])) {
            $checkjs = NULL;
        } elseif ($_COOKIE['ct_checkjs'] == self::GetCheckJSValue()) {
            $checkjs = 1;
        } else {
            $checkjs = 0;
        }

        if (isset($_SERVER['HTTP_USER_AGENT']))
            $user_agent = htmlspecialchars((string)$_SERVER['HTTP_USER_AGENT']);
        else
            $user_agent = NULL;

        if (isset($_SERVER['HTTP_REFERER']))
            $refferrer = htmlspecialchars((string)$_SERVER['HTTP_REFERER']);
        else
            $refferrer = NULL;

        $ct_language = 'en';

        $sender_info = array(
            'cms_lang' => $ct_language,
            'REFFERRER' => $refferrer,
            'post_url' => $refferrer,
            'USER_AGENT' => $user_agent,
            'REFFERRER_PREVIOUS' => isset($_COOKIE['apbct_prev_referer']) ? $_COOKIE['apbct_prev_referer'] : null,
            'cookies_enabled' => self::CookiesTest(),
            'fields_number' => sizeof($arEntity),
        );
        $sender_info = json_encode($sender_info);

        require_once 'lib/cleantalk.class.php';

        $ct = new Cleantalk();
        $ct->work_url = $ct_ws['work_url'];
        $ct->server_url = $ct_ws['server_url'];
        $ct->server_ttl = $ct_ws['server_ttl'];
        $ct->server_changed = $ct_ws['server_changed'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded_for = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? htmlentities($_SERVER['HTTP_X_FORWARDED_FOR']) : '';
        }
        $sender_ip = (!empty($forwarded_for)) ? $forwarded_for : $_SERVER['REMOTE_ADDR'];

        $ct_request = new CleantalkRequest();
        $ct_request->auth_key = $ct_key;
        $ct_request->sender_email = isset($arEntity['sender_email']) ? $arEntity['sender_email'] : '';
        $ct_request->sender_nickname = isset($arEntity['sender_nickname']) ? $arEntity['sender_nickname'] : '';
        $ct_request->sender_ip = isset($arEntity['sender_ip']) ? $arEntity['sender_ip'] : $sender_ip;
        $ct_request->agent = 'magento-127';
        $ct_request->js_on = $checkjs;
        $ct_request->sender_info = $sender_info;
        $ct_request->submit_time = isset($_COOKIE['apbct_timestamp']) ? time() - intval($_COOKIE['apbct_timestamp']) : 0;

        switch ($type) {
            case 'comment':
                $timelabels_key = 'mail_error_comment';

                // Message compilation
                $msg = $arEntity['message_body'];
                $msg = !empty($msg) ? $msg : array();
                $msg = is_array($msg) ? $msg : array($msg);
                if (isset($arEntity['message_title'])) {
                    $msg['apbct_title'] = $arEntity['message_title'];
                }
                $ct_request->message = json_encode($msg);

                // Example compilation
                $example = '';
                $a_example['title'] = isset($arEntity['example_title']) ? $arEntity['example_title'] : '';
                $a_example['body'] = isset($arEntity['example_body']) ? $arEntity['example_body'] : '';
                $a_example['comments'] = isset($arEntity['example_comments']) ? $arEntity['example_comments'] : '';

                // Additional info.
                $post_info = '';
                $a_post_info['comment_type'] = 'comment';

                // JSON format.
                $example = json_encode($a_example);
                $post_info = json_encode($a_post_info);

                // Plain text format.
                if ($example === FALSE) {
                    $example = '';
                    $example .= $a_example['title'] . " \n\n";
                    $example .= $a_example['body'] . " \n\n";
                    $example .= $a_example['comments'];
                }
                if ($post_info === FALSE)
                    $post_info = '';

                // Example text + last N comments in json or plain text format.
                $ct_request->example = $example;
                $ct_request->post_info = $post_info;
                $ct_result = $ct->isAllowMessage($ct_request);
                break;
            case 'register':
                $timelabels_key = 'mail_error_reg';
                $ct_request->tz = isset($arEntity['user_timezone']) ? $arEntity['user_timezone'] : NULL;
                //write a log entry with the request data for checking registration for debugging purposes
                $logger->log(sprintf("Cleantalk API request for registration: %s", json_encode($ct_request)));
                $ct_result = $ct->isAllowUser($ct_request);
        }

        $ret_val = array();
        $ret_val['ct_request_id'] = $ct_result->id;

        if ($ct->server_change)
            self::SetWorkServer(
                $ct->work_url, $ct->server_url, $ct->server_ttl, time()
            );

        // First check errstr flag.
        if (!empty($ct_result->errstr)
            || (!empty($ct_result->inactive) && $ct_result->inactive == 1)
        ) {
            // Cleantalk error so we go default way (no action at all).
            $ret_val['errno'] = 1;
            $err_title = $_SERVER['SERVER_NAME'] . ' - CleanTalk module error';

            if (!empty($ct_result->errstr)) {
                if (preg_match('//u', $ct_result->errstr)) {
                    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $ct_result->errstr);
                } else {
                    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $ct_result->errstr);
                }
            } else {
                if (preg_match('//u', $ct_result->comment)) {
                    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $ct_result->comment);
                } else {
                    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $ct_result->comment);
                }
            }
            $ret_val['errstr'] = $err_str;

            $timedata = FALSE;
            $send_flag = FALSE;
            $insert_flag = FALSE;
            try {
                $timelabels = Mage::getModel('antispam/timelabels');
                $timelabels->load('mail_error');
                $time = $timelabels->getData();
                if (!$time || empty($time)) {
                    $send_flag = TRUE;
                    $insert_flag = TRUE;
                } elseif (time() - 900 > $time['ct_value']) {   // 15 minutes
                    $send_flag = TRUE;
                    $insert_flag = FALSE;
                }
            } catch (Exception $e) {
                $send_flag = FALSE;
                Mage::log('Cannot operate with "cleantalk_timelabels" table.');
            }

            if ($send_flag) {
                Mage::log($err_str);
                if (!$insert_flag)
                    $timelabels->setData('ct_key', 'mail_error');
                $timelabels->setData('ct_value', time());
                $timelabels->save();
                $general_email = Mage::getStoreConfig('trans_email/ident_general/email');

                $mail = Mage::getModel('core/email');
                $mail->setToEmail($general_email);
                $mail->setFromEmail($general_email);
                $mail->setSubject($err_title);
                $mail->setBody($_SERVER['SERVER_NAME'] . "\n\n" . $err_str);
                $mail->setType('text');
                try {
                    $mail->send();
                } catch (Exception $e) {
                    Mage::log('Cannot send CleanTalk module error message to ' . $general_email);
                }
            }

            return $ret_val;
        }

        $ret_val['errno'] = 0;
        if ($ct_result->allow == 1) {
            // Not spammer.
            $ret_val['allow'] = 1;
        } else {
            $ret_val['allow'] = 0;
            $ret_val['ct_result_comment'] = $ct_result->comment;
            // Spammer.
            // Check stop_queue flag.
            if ($type == 'comment' && $ct_result->stop_queue == 0) {
                // Spammer and stop_queue == 0 - to manual approvement.
                $ret_val['stop_queue'] = 0;
            } else {
                // New user or Spammer and stop_queue == 1 - display message and exit.
                $ret_val['stop_queue'] = 1;
            }
        }
        return $ret_val;
    }

    /**
     * CleanTalk inner function - gets working server.
     */
    private static function GetWorkServer()
    {
        $data = false;
        try {
            $server = Mage::getModel('antispam/server');
            $server->load(1);
            $data = $server->getData();
        } catch (Exception $e) {
            Mage::log('Cannot read from with "cleantalk_server" table.');
        }

        if ($data && !empty($data))
            return array(
                'work_url' => $data['work_url'],
                'server_url' => $data['server_url'],
                'server_ttl' => $data['server_ttl'],
                'server_changed' => $data['server_changed'],
            );
        else
            return array(
                'work_url' => 'http://moderate.cleantalk.org',
                'server_url' => 'http://moderate.cleantalk.org',
                'server_ttl' => 0,
                'server_changed' => 0,
            );
    }

    /**
     * Cookies test
     */
    private static function CookiesTest()
    {
        if (isset($_COOKIE['apbct_cookies_test'])) {

            $cookie_test = json_decode(stripslashes($_COOKIE['apbct_cookies_test']), true);

            $check_srting = Mage::getStoreConfig('general/cleantalk/api_key');

            foreach ($cookie_test['cookies_names'] as $cookie_name) {
                $check_srting .= isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : '';
            }
            unset($cokie_name);

            if ($cookie_test['check_value'] == md5($check_srting)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return null;
        }
    }

    /**
     * CleanTalk inner function - sets working server.
     */
    private static function SetWorkServer($work_url = 'http://moderate.cleantalk.org', $server_url = 'http://moderate.cleantalk.org', $server_ttl = 0, $server_changed = 0)
    {
        try {
            $server = Mage::getModel('antispam/server');
            $server->load(1);
            $data = $server->getData();

            if ($data && !empty($data))
                $server->setData('server_id', 1);

            $server->setData('work_url', $work_url);
            $server->setData('server_url', $server_url);
            $server->setData('server_ttl', $server_ttl);
            $server->setData('server_changed', $server_changed);
            $server->save();
        } catch (Exception $e) {
            Mage::log('Cannot write to "cleantalk_server" table.');
        }
    }

    protected function _construct()
    {
        $this->_init('antispam/api');
        $this->logger = Mage::getSingleton('antispam/logger');
    }


}// class Cleantalk_Antispam_Model_Api
