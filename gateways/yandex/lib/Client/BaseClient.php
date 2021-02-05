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


use Exception;
use Psr\Log\LoggerInterface;
use YooKassa\Common\Exceptions\ApiConnectionException;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\AuthorizeException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\JsonException;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Common\LoggerWrapper;
use YooKassa\Common\ResponseObject;
use YooKassa\Helpers\Config\ConfigurationLoader;
use YooKassa\Helpers\Config\ConfigurationLoaderInterface;

class BaseClient
{
    const PAYMENTS_PATH = '/payments';
    const REFUNDS_PATH = '/refunds';
    const WEBHOOKS_PATH = '/webhooks';
    const RECEIPTS_PATH = '/receipts';
    const ME_PATH = '/me';

    /**
     * Имя HTTP заголовка, используемого для передачи idempotence key
     */
    const IDEMPOTENCY_KEY_HEADER = 'Idempotence-Key';

    /**
     * Значение по умолчанию времени ожидания между запросами при отправке повторного запроса в случае получения
     * ответа с HTTP статусом 202
     */
    const DEFAULT_DELAY = 1800;

    /**
     * Значение по умолчанию количества попыток получения информации от API если пришёл ответ с HTTP статусом 202
     */
    const DEFAULT_TRIES_COUNT = 3;

    /**
     * Значение по умолчанию количества попыток получения информации от API если пришёл ответ с HTTP статусом 202
     */
    const DEFAULT_ATTEMPTS_COUNT = 3;

    /**
     * @var null|ApiClientInterface
     */
    protected $apiClient;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var array
     */
    protected $config;

    /**
     * Время через которое будут осуществляться повторные запросы
     * Значение по умолчанию - 1800 миллисекунд.
     * @var int значение в миллисекундах
     */
    protected $timeout;

    /**
     * Количество повторных запросов при ответе API статусом 202
     * Значение по умолчанию 3
     * @var int
     */
    protected $attempts;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param ApiClientInterface|null $apiClient
     * @param ConfigurationLoaderInterface|null $configLoader
     */
    public function __construct(ApiClientInterface $apiClient = null, ConfigurationLoaderInterface $configLoader = null)
    {
        if ($apiClient === null) {
            $apiClient = new CurlClient();
        }

        if ($configLoader === null) {
            $configLoader = new ConfigurationLoader();
        }
        $config       = $configLoader->load()->getConfig();
        $this->setConfig($config);
        $apiClient->setConfig($config);

        $this->attempts  = self::DEFAULT_ATTEMPTS_COUNT;
        $this->apiClient = $apiClient;
    }

    /**
     * @param $login
     * @param $password
     *
     * @return static $this
     */
    public function setAuth($login, $password)
    {
        $this->login    = $login;
        $this->password = $password;

        $this->apiClient
            ->setBearerToken(null)
            ->setShopId($this->login)
            ->setShopPassword($this->password);

        return $this;
    }

    /**
     * @param $token
     *
     * @return $this
     */
    public function setAuthToken($token)
    {
        $this->apiClient
            ->setShopId(null)
            ->setShopPassword(null)
            ->setBearerToken($token);

        return $this;
    }

    /**
     * @return ApiClientInterface
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @param ApiClientInterface $apiClient
     *
     * @return static $this
     */
    public function setApiClient(ApiClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
        $this->apiClient->setConfig($this->config);
        $this->apiClient->setLogger($this->logger);

        return $this;
    }

    /**
     * Устанавливает логгер приложения
     *
     * @param null|callable|object|LoggerInterface $value Инстанс логгера
     */
    public function setLogger($value)
    {
        if ($value === null || $value instanceof LoggerInterface) {
            $this->logger = $value;
        } else {
            $this->logger = new LoggerWrapper($value);
        }
        if ($this->apiClient !== null) {
            $this->apiClient->setLogger($this->logger);
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Установка значение задержки между повторными запросами
     *
     * @param int $timeout
     *
     * @return static
     */
    public function setRetryTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Установка значения количества попыток повторных запросов при статусе 202
     *
     * @param int $attempts
     *
     * @return static
     */
    public function setMaxRequestAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * @param $serializedData
     *
     * @return string
     * @throws Exception
     */
    protected function encodeData($serializedData)
    {
        if ($serializedData === array()) {
            return '{}';
        }

        if (defined('JSON_UNESCAPED_UNICODE')) {
            $encoded = json_encode($serializedData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $encoded = self::_unescaped(json_encode($serializedData));
        }

        if ($encoded === false) {
            $errorCode = json_last_error();
            throw new JsonException("Failed serialize json.", $errorCode);
        }

        return $encoded;
    }

    /**
     * @param string $json
     * @return string|false
     */
    private static function _unescaped($json)
    {
        if ($json === false) {
            return false;
        }

        $json = str_replace('\\/', '/', $json);

        return preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
            return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
        }, $json);
    }

    /**
     * @param ResponseObject $response
     *
     * @return array
     */
    protected function decodeData(ResponseObject $response)
    {
        $resultArray = json_decode($response->getBody(), true);
        if ($resultArray === null) {
            throw new JsonException('Failed to decode response', json_last_error());
        }

        return $resultArray;
    }

    /**
     * @param ResponseObject $response
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    protected function handleError(ResponseObject $response)
    {
        switch ($response->getCode()) {
            case BadApiRequestException::HTTP_CODE:
                throw new BadApiRequestException($response->getHeaders(), $response->getBody());
                break;
            case ForbiddenException::HTTP_CODE:
                throw new ForbiddenException($response->getHeaders(), $response->getBody());
                break;
            case UnauthorizedException::HTTP_CODE:
                throw new UnauthorizedException($response->getHeaders(), $response->getBody());
                break;
            case InternalServerError::HTTP_CODE:
                throw new InternalServerError($response->getHeaders(), $response->getBody());
                break;
            case NotFoundException::HTTP_CODE:
                throw new NotFoundException($response->getHeaders(), $response->getBody());
                break;
            case TooManyRequestsException::HTTP_CODE:
                throw new TooManyRequestsException($response->getHeaders(), $response->getBody());
                break;
            case ResponseProcessingException::HTTP_CODE:
                throw new ResponseProcessingException($response->getHeaders(), $response->getBody());
                break;
            default:
                if ($response->getCode() > 399) {
                    throw new ApiException(
                        'Unexpected response error code',
                        $response->getCode(),
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
        }
    }

    /**
     * Задержка между повторными запросами
     *
     * @param $response
     */
    protected function delay($response)
    {
        $timeout      = $this->timeout;
        $responseData = $this->decodeData($response);
        if ($timeout) {
            $delay = $timeout;
        } else {
            if (isset($responseData['retry_after'])) {
                $delay = $responseData['retry_after'];
            } else {
                $delay = self::DEFAULT_DELAY;
            }
        }
        usleep($delay * 1000);
    }

    /**
     * Выполнение запроса и обработка 202 статуса
     *
     * @param $path
     * @param $method
     * @param $queryParams
     * @param null $httpBody
     * @param array $headers
     *
     * @return mixed|ResponseObject
     * @throws ApiException
     * @throws AuthorizeException
     * @throws ApiConnectionException
     * @throws ExtensionNotFoundException
     */
    protected function execute($path, $method, $queryParams, $httpBody = null, $headers = array())
    {
        $attempts = $this->attempts;
        $response = $this->apiClient->call($path, $method, $queryParams, $httpBody, $headers);

        while (in_array($response->getCode(), array(202, 500)) && $attempts > 0) {
            $this->delay($response);
            $attempts--;
            $response = $this->apiClient->call($path, $method, $queryParams, $httpBody, $headers);
        }

        return $response;
    }
}