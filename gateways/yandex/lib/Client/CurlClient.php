<?php

/**
 * The MIT License
 *
 * Copyright (c) 2020 "YooMoney", NBСO LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YooKassa\Client;

use Psr\Log\LoggerInterface;
use YooKassa\Common\Exceptions\ApiConnectionException;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\AuthorizeException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\ResponseObject;
use YooKassa\Helpers\RawHeadersParser;

/**
 * Class CurlClient
 * @package YooKassa\Client
 */
class CurlClient implements ApiClientInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $shopPassword;

    /**
     * @var string
     */
    private $bearerToken;

    /**
     * @var int
     */
    private $timeout = 80;

    /**
     * @var int
     */
    private $connectionTimeout = 30;

    /**
     * @var string
     */
    private $proxy;

    /** @var UserAgent */
    private $userAgent;

    /**
     * @var bool
     */
    private $keepAlive = true;

    /**
     * @var array
     */
    private $defaultHeaders = array(
        'Content-Type' => 'application/json',
        'Accept'       => 'application/json',
    );

    /**
     * @var resource
     */
    private $curl;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * CurlClient constructor.
     */
    public function __construct()
    {
        $this->userAgent = new UserAgent();
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     *
     * @param $path
     * @param $method
     * @param $queryParams
     * @param null $httpBody
     * @param array $headers
     *
     * @return ResponseObject
     * @throws ApiConnectionException
     * @throws ApiException
     * @throws AuthorizeException
     * @throws ExtensionNotFoundException
     */
    public function call($path, $method, $queryParams, $httpBody = null, $headers = array())
    {
        $headers = $this->prepareHeaders($headers);

        $this->logRequestParams($path, $method, $queryParams, $httpBody, $headers);

        $url = $this->prepareUrl($path, $queryParams);

        $this->prepareCurl($method, $httpBody, $this->implodeHeaders($headers), $url);

        list($httpHeaders, $httpBody, $responseInfo) = $this->sendRequest();

        if (!$this->keepAlive) {
            $this->closeCurlConnection();
        }

        $this->logResponse($httpBody, $responseInfo, $httpHeaders);

        return new ResponseObject(array(
            'code'    => $responseInfo['http_code'],
            'headers' => $httpHeaders,
            'body'    => $httpBody,
        ));
    }

    /**
     * @param $optionName
     * @param $optionValue
     *
     * @return bool
     */
    public function setCurlOption($optionName, $optionValue)
    {
        return curl_setopt($this->curl, $optionName, $optionValue);
    }


    /**
     * @return resource
     * @throws ExtensionNotFoundException
     */
    private function initCurl()
    {
        if (!extension_loaded('curl')) {
            throw new ExtensionNotFoundException('curl');
        }

        if (!$this->curl || !$this->keepAlive) {
            $this->curl = curl_init();
        }

        return $this->curl;
    }

    /**
     * Close connection
     */
    public function closeCurlConnection()
    {
        if ($this->curl !== null) {
            curl_close($this->curl);
        }
    }

    /**
     * @return array
     * @throws ApiConnectionException
     */
    public function sendRequest()
    {
        $response       = curl_exec($this->curl);
        $httpHeaderSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $httpHeaders    = RawHeadersParser::parse(substr($response, 0, $httpHeaderSize));
        $httpBody       = substr($response, $httpHeaderSize);
        $responseInfo   = curl_getinfo($this->curl);
        $curlError      = curl_error($this->curl);
        $curlErrno      = curl_errno($this->curl);
        if ($response === false) {
            $this->handleCurlError($curlError, $curlErrno);
        }

        return array($httpHeaders, $httpBody, $responseInfo);
    }

    /**
     * @param $method
     * @param $httpBody
     */
    public function setBody($method, $httpBody)
    {

        $this->setCurlOption(CURLOPT_CUSTOMREQUEST, $method);
        if(!empty($httpBody)) {
            $this->setCurlOption(CURLOPT_POSTFIELDS, $httpBody);
        }
    }

    /**
     * @param mixed $shopId
     *
     * @return CurlClient
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * @param mixed $shopPassword
     *
     * @return CurlClient
     */
    public function setShopPassword($shopPassword)
    {
        $this->shopPassword = $shopPassword;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * @param mixed $connectionTimeout
     */
    public function setConnectionTimeout($connectionTimeout)
    {
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @return string
     * @since 1.0.14
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @param string $proxy
     *
     * @since 1.0.14
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return UserAgent
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $bearerToken
     *
     * @return static $this
     */
    public function setBearerToken($bearerToken)
    {
        $this->bearerToken = $bearerToken;

        return $this;
    }

    /**
     * @param bool $keepAlive
     *
     * @return CurlClient
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = $keepAlive;

        return $this;
    }

    /**
     * @param string $error
     * @param int $errno
     *
     * @throws ApiConnectionException
     */
    private function handleCurlError($error, $errno)
    {
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = 'Could not connect to YooKassa API. Please check your internet connection and try again.';
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = 'Could not verify SSL certificate.';
                break;
            default:
                $msg = 'Unexpected error communicating.';
        }
        $msg .= "\n\n(Network error [errno $errno]: $error)";
        throw new ApiConnectionException($msg);
    }

    /**
     * @return mixed
     */
    private function getUrl()
    {
        $config = $this->config;

        return $config['url'];
    }

    /**
     * @param $headers
     *
     * @return array
     * @throws AuthorizeException
     */
    private function prepareHeaders($headers)
    {
        $headers = array_merge($this->defaultHeaders, $headers);

        $headers[UserAgent::HEADER] = $this->getUserAgent()->getHeaderString();

        if ($this->shopId && $this->shopPassword) {
            $encodedAuth = base64_encode($this->shopId . ':' . $this->shopPassword);
            $headers['Authorization'] = 'Basic ' . $encodedAuth;
        } else if ($this->bearerToken) {
            $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
        }

        if (empty($headers['Authorization'])) {
            throw new AuthorizeException('Authorization headers not set');
        }

        return $headers;
    }

    /**
     * @param array $headers
     * @return array
     */
    private function implodeHeaders($headers)
    {
        return array_map(function ($key, $value) { return $key . ':' . $value; }, array_keys($headers), $headers);
    }

    /**
     * @param $path
     * @param $method
     * @param $queryParams
     * @param $httpBody
     * @param $headers
     */
    private function logRequestParams($path, $method, $queryParams, $httpBody, $headers)
    {
        if ($this->logger !== null) {
            $message = 'Send request: ' . $method . ' ' . $path;
            $context = array();
            if (!empty($queryParams)) {
                $context['_params'] = $queryParams;
            }
            if (!empty($httpBody)) {
                $data = json_decode($httpBody, true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    $data = $httpBody;
                }
                $context['_body'] = $data;
            }
            if (!empty($headers)) {
                $context['_headers'] = $headers;
            }
            $this->logger->info($message, $context);
        }
    }

    /**
     * @param $path
     * @param $queryParams
     *
     * @return string
     */
    private function prepareUrl($path, $queryParams)
    {
        $url = $this->getUrl() . $path;

        if (!empty($queryParams)) {
            $url = $url . '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * @param $httpBody
     * @param $responseInfo
     * @param $httpHeaders
     */
    private function logResponse($httpBody, $responseInfo, $httpHeaders)
    {
        if ($this->logger !== null) {
            $message = 'Response with code ' . $responseInfo['http_code'] . ' received.';
            $context = array();
            if (!empty($httpBody)) {
                $data = json_decode($httpBody, true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    $data = $httpBody;
                }
                $context['_body'] = $data;
            }
            if (!empty($httpHeaders)) {
                $context['_headers'] = $httpHeaders;
            }
            $this->logger->info($message, $context);
        }
    }

    /**
     * @param $method
     * @param $httpBody
     * @param $headers
     * @param $url
     * @throws ExtensionNotFoundException
     */
    private function prepareCurl($method, $httpBody, $headers, $url)
    {
        $this->initCurl();

        $this->setCurlOption(CURLOPT_URL, $url);

        $this->setCurlOption(CURLOPT_RETURNTRANSFER, true);

        $this->setCurlOption(CURLOPT_HEADER, true);

        $this->setCurlOption(CURLOPT_BINARYTRANSFER, true);

        if ($this->proxy) {
            $this->setCurlOption(CURLOPT_PROXY, $this->proxy);
            $this->setCurlOption(CURLOPT_HTTPPROXYTUNNEL, true);
        }

        $this->setBody($method, $httpBody);

        $this->setCurlOption(CURLOPT_HTTPHEADER, $headers);

        $this->setCurlOption(CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);

        $this->setCurlOption(CURLOPT_TIMEOUT, $this->timeout);
    }
}