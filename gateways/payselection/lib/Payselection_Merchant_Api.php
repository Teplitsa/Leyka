<?php if( !defined('WPINC') ) die;

class Payselection_Merchant_Api
{
    private $site_id;
    private $secret_key;
    private $host;
    private $create_host;

    public function __construct($site_id, $secret_key, $host, $create_host)
    {
        $this->site_id = $site_id;
        $this->secret_key = $secret_key;
        $this->host = untrailingslashit(esc_url($host));
        $this->create_host = untrailingslashit(esc_url($create_host));
    }

    /**
     * request Send request to API server
     *
     * @param  string $path - API path
     * @param  array|bool $data - Request DATA
     * @return WP_Error|string
     */
    protected function request(string $host, string $path, $data = false, $method = "GET")
    {
        $bodyJSON = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : "";

        $requestID = self::guidv4();

        $signBody = $method . PHP_EOL . "/" . $path . PHP_EOL . $this->site_id . PHP_EOL . $requestID . PHP_EOL . $bodyJSON;

        $headers = [
            "X-SITE-ID" => $this->site_id,
            "X-REQUEST-ID" => $requestID,
            "X-REQUEST-SIGNATURE" => self::getSignature($signBody, $this->secret_key),
        ];

        $url = $host . "/" . $path;
        $params = [
            "timeout" => 30,
            "redirection" => 5,
            "httpversion" => "1.0",
            "blocking" => true,
            "headers" => $headers,
            "body" => $bodyJSON,
        ];

        $response = $method === 'POST' ? wp_remote_post($url, $params) : wp_remote_get($url, $params);

        if (is_wp_error($response)) {
            return $response;
        }

        // Decode response
        $response["body"] = json_decode($response["body"], true);

        $code = $response["response"]["code"];

        if ($code === 200 || $code === 201) {
            return $response["body"];
        }

        return new \WP_Error("payselection_request_error", $response["body"]["Code"] . ($response["body"]["Description"] ? " " . $response["body"]["Description"] : ""));
    }

    /**
     * guidv4 Create uuid unique id
     * Ref: https://www.uuidgenerator.net/dev-corner/php
     *
     * @param  array|null $data - Random 16 bytes
     * @return string
     */
    protected static function guidv4($data = null)
    {
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
    }

    /**
     * getSignature Get signature by request body and key
     *
     * @param  string $body
     * @param  string $secretKey
     * @return string
     */
    protected function getSignature(string $body, string $secretKey)
    {
        if (empty($body)) {
            return ";";
        }

        $hash = hash_hmac("sha256", $body, $secretKey, false);
        return $hash;
    }
    
    /**
     * getPaymentLink Get payment link
     *
     * @param  array $data - Request params
     * @return WP_Error|string
     */
    public function getPaymentLink(array $data = [])
    {
        return $this->request($this->create_host, 'webpayments/create', $data, 'POST');
    }
    
    /**
     * charge Charge payment
     *
     * @param  array $data - Request params
     * @return WP_Error|string
     */
    public function charge(array $data = [])
    {
        return $this->request($this->host, 'payments/charge', $data, 'POST');
    }
    
    /**
     * cancel Cancel payment
     *
     * @param  array $data - Request params
     * @return WP_Error|string
     */
    public function cancel(array $data = [])
    {
        return $this->request($this->host, 'payments/cancellation', $data, 'POST');
    }

    /**
     * Unsubscribe payment
     *
     * @param  array $data - Request params
     * @return WP_Error|string
     */
    public function unsubscribe(array $data = [])
    {
        return $this->request($this->host, 'payments/unsubscribe', $data, 'POST');
    }

    /**
     * Rebill payment
     *
     * @param  array $data - Request params
     * @return WP_Error|string
     */
    public function rebill(array $data = [])
    {
        return $this->request($this->host, 'payments/requests/rebill', $data, 'POST');
    }

    /**
     * Refund payment
     *
     * @param  array $data - Request params
     * @return WP_Error|string
     */
    public function refund(array $data = [])
    {
        return $this->request($this->host, 'payments/refund', $data, 'POST');
    }

     /**
     * handle Webhook handler
     *
     * @return void
     */
    public static function verify_header_signature($request, $site_id, $secret_key)
    {
        $headers = getallheaders();

        if (
            empty($request) ||
            empty($headers['X-SITE-ID']) ||
            empty($headers['X-WEBHOOK-SIGNATURE'])
        ) {
            return new \WP_Error('payselection_donation_webhook_error', __('A call to your Payselection callbacks URL was made with a missing required parameter.', 'leyka'));
        }

        if ($site_id != $headers['X-SITE-ID'] ) {
            return new \WP_Error(
                'payselection_donation_webhook_site_id_error',
                sprintf(__('A call to your Payselection callback was called with wrong site id. Site id from request: %s, Site id from options: %s', 'leyka'), $headers['X-SITE-ID'], $site_id)
            );
        }
        
        // Check signature
        $signBody = $_SERVER['REQUEST_METHOD'] . PHP_EOL . home_url('/leyka/service/payselection/response') . PHP_EOL . $site_id . PHP_EOL . $request;
        $signCalculated = self::getSignature($signBody, $secret_key);

        if ($headers['X-WEBHOOK-SIGNATURE'] !== $signCalculated) {
            return new \WP_Error(
                'payselection_donation_webhook_signature_error',
                sprintf(__('A call to your Payselection callback was called with wrong digital signature. It may mean that someone is trying to hack your payment website. Signature from request: %s, Signature calculated: %s', 'leyka'), $headers['X-WEBHOOK-SIGNATURE'], $signCalculated)
            );
        }

        return true;
    }

}