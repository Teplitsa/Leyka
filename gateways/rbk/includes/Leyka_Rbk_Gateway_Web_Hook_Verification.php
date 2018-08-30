<?php

class Leyka_Rbk_Gateway_Web_Hook_Verification
{
    /**
     * Openssl verify
     */
    const OPENSSL_VERIFY_SIGNATURE_IS_CORRECT = 1;
    const OPENSSL_VERIFY_SIGNATURE_IS_INCORRECT = 0;
    const OPENSSL_VERIFY_ERROR = -1;

    const SIGNATURE = 'HTTP_CONTENT_SIGNATURE';
    const SIGNATURE_ALG = 'alg';
    const SIGNATURE_DIGEST = 'digest';
    const SIGNATURE_PATTERN = "|alg=(\S+);\sdigest=(.*)|i";


    public static function key_prepare($key)
    {

        if (false !== $key) {
            $key = str_replace(array('-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'), '', $key);
            $key = str_replace(' ', PHP_EOL, $key);
            $key = '-----BEGIN PUBLIC KEY-----' . $key . '-----END PUBLIC KEY-----';

            return $key;
        }

        return false;
    }

    public static function verify_header_signature($content)
    {
        $key = self::key_prepare(get_option('leyka_rbk_api_web_hook_key', false));

        if (empty($_SERVER[self::SIGNATURE])) {
            new WP_Error(
                'Leyka_webhook_error',
                'Webhook notification signature missing'
            );
        }

        $params_signature = self::get_parameters_content_signature(
            $_SERVER[self::SIGNATURE]
        );

        if (empty($params_signature[self::SIGNATURE_ALG])) {
            return new WP_Error(
                'Leyka_webhook_error',
                'Missing required parameter ' . self::SIGNATURE_ALG
            );
        }

        if (empty($params_signature[self::SIGNATURE_DIGEST])) {
            return new WP_Error(
                'Leyka_webhook_error',
                'Missing required parameter ' . self::SIGNATURE_DIGEST
            );
        }

        $signature = self::urlsafe_b64decode(
            $params_signature[self::SIGNATURE_DIGEST]
        );

        if (!self::verification_signature(
            $content, $signature, trim($key))) {
            return new WP_Error(
                'Leyka_webhook_error',
                'Webhook notification signature mismatch'
            );
        }

        return true;

    }

    public static function verification_signature($data = '', $signature = '', $public_key = '')
    {
        if (empty($data) || empty($signature) || empty($public_key)) {
            return false;
        }
        $public_key_id = openssl_get_publickey($public_key);
        if (empty($public_key_id)) {
            return false;
        }
        $verify = openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_SHA256);

        return ($verify == static::OPENSSL_VERIFY_SIGNATURE_IS_CORRECT);
    }

    public static function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }

    public static function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);

        return str_replace(array('+', '/'), array('-', '_'), $data);
    }

    public static function get_parameters_content_signature($content_signature)
    {
        preg_match_all(static::SIGNATURE_PATTERN, $content_signature, $matches, PREG_PATTERN_ORDER);
        $params = array();
        $params[static::SIGNATURE_ALG] = !empty($matches[1][0]) ? $matches[1][0] : '';
        $params[static::SIGNATURE_DIGEST] = !empty($matches[2][0]) ? $matches[2][0] : '';

        return $params;
    }
}
