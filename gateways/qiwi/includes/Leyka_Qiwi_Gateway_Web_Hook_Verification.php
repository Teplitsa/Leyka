<?php if (!defined('WPINC')) {
    die;
}

class Leyka_Qiwi_Gateway_Web_Hook_Verification {

    const VALUE_SEPARATOR = '|';
    const DEFAULT_ALGORITHM = 'sha256';

    /**
     * @param string $signature The signature
     * @param object|array $notificationBody The notification body
     * @param string $merchantSecret The merchant key for validating signature
     * @return bool Signature is valid or not
     */
    public static function checkNotificationSignature($signature, $notificationBody, $merchantSecret) {

        $processedNotificationData = [
            'billId' => (string)isset($notificationBody['bill']['billId']) ? $notificationBody['bill']['billId'] : '',
            'amount.value' => (string)isset($notificationBody['bill']['amount']['value']) ? self::normalizeAmount($notificationBody['bill']['amount']['value']) : 0,
            'amount.currency' => (string)isset($notificationBody['bill']['amount']['currency']) ? $notificationBody['bill']['amount']['currency'] : '',
            'siteId' => (string)isset($notificationBody['bill']['siteId']) ? $notificationBody['bill']['siteId'] : '',
            'status' => (string)isset($notificationBody['bill']['status']['value']) ? $notificationBody['bill']['status']['value'] : ''
        ];
        ksort($processedNotificationData);
        $processedNotificationDataKeys = join(self::VALUE_SEPARATOR, $processedNotificationData);
        $hash = hash_hmac(self::DEFAULT_ALGORITHM, $processedNotificationDataKeys, $merchantSecret);

        return $hash === $signature;

    }

    /**
     * @param string|float|int $amount The value
     * @return string The API value
     */
    public static function normalizeAmount($amount = 0) {
        return number_format(round(floatval($amount), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
    }

}