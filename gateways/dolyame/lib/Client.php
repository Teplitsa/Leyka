<?php

namespace Dolyame\Payment;

class Client
{
	protected $url = 'https://partner.dolyame.ru/v1/orders/';

	protected $login       = '';
	protected $password    = '';
	protected $certPath    = '';
	protected $keyPath     = '';
	protected $certPass    = '';
	protected $logger      = false;
	protected $tmpCertFile = null;
	protected $tmpKeyFile  = null;
	protected $useFileRequestHandler = false;

	public function __construct(string $login, string $password)
	{
		$this->login    = $login;
		$this->password = $password;
	}

	public function setCertPath(string $certPath)
	{
		if (strpos($certPath, "-----BEGIN CERTIFICATE-----") !== false) {
			$this->tmpCertFile = tmpfile();
			fwrite($this->tmpCertFile, trim($certPath));
			$tempPath = stream_get_meta_data($this->tmpCertFile);
			$certPath = $tempPath['uri'];
		}

		$this->certPath = $certPath;
		if (!file_exists($this->certPath)) {
			throw new \Exception('Cert path did\'t exist: ' . $this->certPath);
		}
		if (!is_readable($this->certPath)) {
			throw new \Exception('Can\'t read cert file: ' . $this->certPath);
		}
	}

	public function setKeyPath(string $keyPath)
	{
		if (
			strpos($keyPath, "-----END PRIVATE KEY-----") !== false
			||
			strpos($keyPath, '-----END RSA PRIVATE KEY-----') !== false
			) {
			$this->tmpKeyFile = tmpfile();
			fwrite($this->tmpKeyFile, trim($keyPath));
			$tempPath = stream_get_meta_data($this->tmpKeyFile);
			$keyPath = $tempPath['uri'];
		}
		$this->keyPath = $keyPath;
		if (!file_exists($this->keyPath)) {
			throw new \Exception('Key path did\'t exist: ' . $this->keyPath);
		}

		if (!is_readable($this->keyPath)) {
			throw new \Exception('Can\'t read key file: ' . $this->keyPath);
		}
	}

	public function useFileRequestHandler()
	{
		$this->useFileRequestHandler = true;
	}

	public function setCertPass(string $certPass)
	{
		$this->certPass = $certPass;
	}

	public function setLogger($logger)
	{
		$this->logger = $logger;
	}

	public function generateCorrelationId()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	public function create(array $data, string $correlationId = '')
	{
		$data['order']['id'] = self::prepareOrderId($data['order']['id']);
		return $this->execute('create', $data, 'POST', $correlationId);
	}

	public function cancel(string $orderId, string $correlationId = '')
	{
		$orderId = self::prepareOrderId($orderId);
		return $this->execute($orderId . '/cancel', [], 'POST', $correlationId);
	}

	public function commit(string $orderId, array $data, string $correlationId = '')
	{
		$orderId = self::prepareOrderId($orderId);
		return $this->execute($orderId . '/commit', $data, 'POST', $correlationId);
	}

	public function info(string $orderId, string $correlationId = '')
	{
		$orderId = self::prepareOrderId($orderId);
		return $this->execute($orderId . '/info', [], 'GET', $correlationId);
	}

	public function refund(string $orderId, array $data, string $correlationId = '')
	{
		$orderId = self::prepareOrderId($orderId);
		return $this->execute($orderId . '/refund', $data, 'POST', $correlationId);
	}

	protected function execute(string $action, array $data, string $method, string $correlationId)
	{
		if ($correlationId === '') {
			$correlationId = $this->generateCorrelationId();
		}
		if ($this->useFileRequestHandler) {
			return $this->fileRequestHandler($action, $data, $method, $correlationId);
		}
		return $this->curlRequestHandler($action, $data, $method, $correlationId);
	}

	protected function curlRequestHandler(string $action, array $data, string $method, string $correlationId)
	{

		$headers = [
			"Content-Type"     => "application/json",
			"X-Correlation-ID" => $correlationId,
			"Authorization"    => "Basic " . base64_encode("{$this->login}:{$this->password}"),
		];

		$responseHeaders = '';
		if (!function_exists("curl_init")) {
			throw new \Exception("Curl error");
		}

		$request = new \WP_Http_Curl();
		$url     = $this->url . $action;
		$params  = [
			'headers' => $headers,
			'method'  => $method,
		];
		if ($this->certPath) {
			add_action('http_api_curl', function ($ch) {
				$this->addCurlCert($ch);
			}, 10, 1);

		}

		if (!empty($data) || $method == 'POST') {
			$encodedData    = $this->encode($data);
			$params['body'] = $encodedData;
		}

		$result = $request->request($url, $params);
		if (is_wp_error($result)) {
			throw new \Exception('Request error: ' . $result->get_error_message());
		}

		$code = $result['response']['code'];

		if ($this->logger) {
			$context = array('source' => 'dolyame-payment');
			$this->logger->info('url' . ' = ' . $url, $context);
			unset($params['headers']['Authorization']);
			$this->logger->info('request' . ' = ' . wp_json_encode($params), $context);
			$this->logger->info('response' . ' = ' . $result['body'], $context);
		}

		$response = json_decode($result['body'], true);
		if ($code == 200) {
			return $response;
		} elseif ($code == 429) {
			sleep($result['headers']['X-Retry-After']);
			return $this->execute($action, $data, $method, $correlationId);
		}

		$error = 'Error: ' . $code;

		if (isset($response['type']) && $response['type'] == 'error') {
			$error .= ' ' . $response['description'];
		}
		if (isset($response['message'])) {
			$error .= ' ' . $response['message'];
		}
		if (!empty($response['details'])) {
			$list = array_map(
				function ($key, $value) {return "$key - $value";},
				array_keys($response['details']),
				array_values($response['details'])
			);
			$error .= ': ' . implode($list);
		}

		if (!$response) {
			$error .= $result['body'];
		}
		throw new \Exception($error, $code);
	}

	protected function fileRequestHandler($action, $data, $method, $correlationId)
	{
		$headers = [
			"Content-Type: application/json",
			"X-Correlation-ID: $correlationId",
			"Authorization: Basic " . base64_encode("{$this->login}:{$this->password}"),
		];

		$streamOptions = [
			'http' => [
				'method'        => "GET",
				'header'        => implode("\r\n", $headers),
				'ignore_errors' => true,
			],
		];

		if ($this->certPath) {
			$streamOptions['ssl'] = [
				'verify_peer' => true,
				'local_cert'  => $this->certPath,
				'local_pk'    => $this->keyPath,
			];
		}
		$encodedData = '';
		if (!empty($data) || $method == 'POST') {
			$encodedData                      = $this->encode($data);
			$streamOptions['http']['method']  = 'POST';
			$streamOptions['http']['content'] = $encodedData;
		}

		$context = stream_context_create($streamOptions);
		$url     = $this->url . $action;
		$out     = file_get_contents($url, false, $context);

		$statusLine = $http_response_header[0];
		preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
		$code = $match[1];

		if ($this->logger) {
			$this->logger->info($method . ' ' . $action);
			$this->logger->info('request' . ' = ' . wp_json_encode($encodedData));
			$this->logger->info('response' . ' = ' . $code . ':' . $out);
		}

		$response = json_decode($out, true);
		if ($code == 200) {
			return $response;
		} elseif ($code == 429) {
			$headers = $this->parseHeadersToArray(implode("\r\n", $http_response_header));
			sleep($headers['X-Retry-After']);
			return $this->execute($action, $data, $method, $correlationId);
		}

		$error = 'Error: ' . $code;

		if (isset($response['type']) && $response['type'] == 'error') {
			$error .= ' ' . $response['description'];
		}
		if (isset($response['message'])) {
			$error .= ' ' . $response['message'];
		}
		if (!empty($response['details'])) {
			$list = array_map(
				function ($key, $value) {return "$key - $value";},
				array_keys($response['details']),
				array_values($response['details'])
			);
			$error .= ': ' . implode($list);
		}

		if (!$response) {
			$error .= $out;
		}
		throw new \Exception($error, $code);
	}

	protected function parseHeadersToArray($rawHeaders)
	{
		$lines   = explode("\r\n", $rawHeaders);
		$headers = [];
		foreach ($lines as $line) {
			if (strpos($line, ':') === false) {
				continue;
			}
			list($key, $value) = explode(': ', $line);
			$headers[$key]     = $value;
		}
		return $headers;
	}

	private function addCurlCert($ch)
	{
		curl_setopt($ch, CURLOPT_SSLCERT, $this->certPath);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->keyPath);
	}

	protected function encode(array $data)
	{
		$result = wp_json_encode($data);
		$error  = json_last_error();
		if ($error != JSON_ERROR_NONE) {
			throw new \Exception('JSON Error: ' . json_last_error_msg());
		}
		return $result;
	}

	public static function prepareOrderId(string $orderId)
	{
		$orderId = str_replace(['/', '#', '?', '|', ' '], ['-'], $orderId);
		return $orderId;
	}

}
