<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Rbk_Gateway_Webhook_Verification class
 */

class Leyka_Rbk_Gateway_Webhook_Verification {

    /**
     * Openssl verify
     */

    const SIGNATURE = 'HTTP_CONTENT_SIGNATURE';
    const SIGNATURE_ALG = 'alg';
    const SIGNATURE_DIGEST = 'digest';
    const SIGNATURE_PATTERN = "|alg=(\S+);\sdigest=(.*)|i";

    public function verify_header_signature($content) {

        $params_signature = false;

        if( !isset($_SERVER[self::SIGNATURE]) ) {
            new WP_Error(
                'Leyka_webhook_error',
                'Webhook notification signature missing'
            );
        }

        if(isset($_SERVER[self::SIGNATURE])) {
            $params_signature = $this->_get_parameters_content_signature($_SERVER[self::SIGNATURE]);
        }

        if(empty($params_signature[self::SIGNATURE_ALG])) {
            return new WP_Error(
                'Leyka_webhook_error',
                'Missing required parameter ' . self::SIGNATURE_ALG
            );
        }

        if(empty($params_signature[self::SIGNATURE_DIGEST])) {
            return new WP_Error(
                'Leyka_webhook_error',
                'Missing required parameter ' . self::SIGNATURE_DIGEST
            );
        }

        if( !$this->_verification_signature(
            $content,
            $this->_urlsafe_b64decode($params_signature[self::SIGNATURE_DIGEST]),
            $this->_get_webhook_public_key()
        )) {
            return new WP_Error(
                'Leyka_webhook_error',
                'Webhook notification signature mismatch'
            );
        }

        return true;

    }

    protected function _get_webhook_public_key() {

        $public_key = str_replace(
            array('-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'),
            '',
            leyka_options()->opt('rbk_api_web_hook_key')
        );

        return '-----BEGIN PUBLIC KEY-----'.str_replace(' ', PHP_EOL, $public_key).'-----END PUBLIC KEY-----';

    }

    protected function _verification_signature($data = '', $signature = '', $public_key = '') {

        if( !$data || !$signature || !$public_key ) {
            return false;
        }

        $public_key_id = openssl_get_publickey($public_key);
        if( !$public_key_id ) {
            return false;
        }

        return openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_SHA256) == 1;

    }

    protected function _urlsafe_b64decode($string) {

        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;

        if($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);

    }

    protected function _urlsafe_b64encode($string) {

        $data = base64_encode($string);
        return str_replace(array('+', '/'), array('-', '_'), $data);

    }

    protected function _get_parameters_content_signature($content_signature) {

        preg_match_all(static::SIGNATURE_PATTERN, $content_signature, $matches, PREG_PATTERN_ORDER);
        return array(
            static::SIGNATURE_ALG => empty($matches[1][0]) ? '' : $matches[1][0],
            static::SIGNATURE_DIGEST => empty($matches[2][0]) ? '' : $matches[2][0],
        );

    }

}
