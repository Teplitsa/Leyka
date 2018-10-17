<?php
/**
 * User: vladimir rambo petrozavodsky
 * Date: 13.10.2018
 */

class  Leyka_Qiwi_Gateway_Web_Hook_Verification
{
    /** @var string The default separator */
    const VALUE_SEPARATOR = '|';

    /** @var string The default hash algorithm */
    const DEFAULT_ALGORITHM = 'sha256';

    /**
     * Checks notification data signature.
     *
     * @param string $signature The signature
     * @param object|array $notificationBody The notification body
     * @param string $merchantSecret The merchant key for validating signature
     * @return bool Signature is valid or not
     */
    public static function checkNotificationSignature($signature, $notificationBody, $merchantSecret)
    {
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
     * Normalize amount.
     *
     * @param string|float|int $amount The value
     * @return string The API value
     */
    public static function normalizeAmount($amount = 0)
    {
        return number_format(
            round(floatval($amount), 2, PHP_ROUND_HALF_DOWN),
            2,
            '.',
            ''
        );
    }

}