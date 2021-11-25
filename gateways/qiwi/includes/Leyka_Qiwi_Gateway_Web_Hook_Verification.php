<?php if (!defined('WPINC')) { die; }

class Leyka_Qiwi_Gateway_Web_Hook_Verification {

    const VALUE_SEPARATOR = '|';
    const DEFAULT_ALGORITHM = 'sha256';

    /**
     * @param string $signature The signature
     * @param object|array $notification_body The notification body
     * @param string $secret The merchant secret key for validating signature
     * @return bool Signature is valid or not
     */
    public static function check_notification_signature($signature, $notification_body, $secret) {

        $processed_notification_data = [
            'billId' => (string)isset($notification_body['bill']['billId']) ? $notification_body['bill']['billId'] : '',
            'amount.value' => (string)isset($notification_body['bill']['amount']['value']) ?
                number_format(round($notification_body['bill']['amount']['value'], 2, PHP_ROUND_HALF_DOWN), 2, '.', '') : 0,
            'amount.currency' => (string)isset($notification_body['bill']['amount']['currency']) ?
                $notification_body['bill']['amount']['currency'] : '',
            'siteId' => (string)isset($notification_body['bill']['siteId']) ? $notification_body['bill']['siteId'] : '',
            'status' => (string)isset($notification_body['bill']['status']['value']) ?
                $notification_body['bill']['status']['value'] : '',
        ];
        ksort($processed_notification_data);

        $hash = hash_hmac(self::DEFAULT_ALGORITHM, join(self::VALUE_SEPARATOR, $processed_notification_data), $secret);

        return $hash === $signature;

    }

}