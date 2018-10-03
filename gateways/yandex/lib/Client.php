<?php

/**
 * The MIT License
 *
 * Copyright (c) 2017 NBCO Yandex.Money LLC
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

namespace YandexCheckout;

use Psr\Log\LoggerInterface;
use YandexCheckout\Common\Exceptions\ApiException;
use YandexCheckout\Common\Exceptions\BadApiRequestException;
use YandexCheckout\Common\Exceptions\ForbiddenException;
use YandexCheckout\Common\Exceptions\JsonException;
use YandexCheckout\Common\Exceptions\InternalServerError;
use YandexCheckout\Common\Exceptions\NotFoundException;
use YandexCheckout\Common\Exceptions\ResponseProcessingException;
use YandexCheckout\Common\Exceptions\TooManyRequestsException;
use YandexCheckout\Common\Exceptions\UnauthorizedException;
use YandexCheckout\Common\HttpVerb;
use YandexCheckout\Common\LoggerWrapper;
use YandexCheckout\Common\ResponseObject;
use YandexCheckout\Helpers\Config\ConfigurationLoader;
use YandexCheckout\Helpers\Config\ConfigurationLoaderInterface;
use YandexCheckout\Helpers\TypeCast;
use YandexCheckout\Helpers\UUID;
use YandexCheckout\Model\PaymentInterface;
use YandexCheckout\Request\PaymentOptionsRequest;
use YandexCheckout\Request\PaymentOptionsRequestInterface;
use YandexCheckout\Request\PaymentOptionsRequestSerializer;
use YandexCheckout\Request\PaymentOptionsResponse;
use YandexCheckout\Request\Payments\CreatePaymentRequest;
use YandexCheckout\Request\Payments\CreatePaymentRequestInterface;
use YandexCheckout\Request\Payments\CreatePaymentResponse;
use YandexCheckout\Request\Payments\CreatePaymentRequestSerializer;
use YandexCheckout\Request\Payments\Payment\CancelResponse;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequestInterface;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequestSerializer;
use YandexCheckout\Request\Payments\Payment\CreateCaptureResponse;
use YandexCheckout\Request\Payments\PaymentResponse;
use YandexCheckout\Request\Payments\PaymentsRequest;
use YandexCheckout\Request\Payments\PaymentsRequestInterface;
use YandexCheckout\Request\Payments\PaymentsRequestSerializer;
use YandexCheckout\Request\Payments\PaymentsResponse;
use YandexCheckout\Request\Refunds\CreateRefundRequest;
use YandexCheckout\Request\Refunds\CreateRefundRequestInterface;
use YandexCheckout\Request\Refunds\CreateRefundRequestSerializer;
use YandexCheckout\Request\Refunds\CreateRefundResponse;
use YandexCheckout\Request\Refunds\RefundResponse;
use YandexCheckout\Request\Refunds\RefundsRequest;
use YandexCheckout\Request\Refunds\RefundsRequestInterface;
use YandexCheckout\Request\Refunds\RefundsRequestSerializer;
use YandexCheckout\Request\Refunds\RefundsResponse;

/**
 * Класс клиента API
 *
 * @package YandexCheckout
 *
 * @since 1.0.1
 */
class Client
{
    const PAYMENTS_PATH = '/payments';
    const REFUNDS_PATH = '/refunds';
    /**
     * Текущая версия библиотеки
     */
    const SDK_VERSION = '1.0.18';

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
     * @var null|Client\ApiClientInterface
     */
    protected $apiClient;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $config;

    /**
     * Время через которое будут осуществляться повторные запросы
     * Значение по умолчанию - 1800 миллисекунд.
     * @link https://kassa.yandex.ru/docs/checkout-api/?php#asinhronnost
     * @var int значение в миллисекундах
     */
    private $timeout;

    /**
     * Количество повторных запросов при ответе API статусом 202
     * Значение по умолчанию 3
     * @link https://kassa.yandex.ru/docs/checkout-api/?php#asinhronnost
     * @var int
     */
    private $attempts;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Client\ApiClientInterface|null $apiClient
     * @param ConfigurationLoaderInterface|null $configLoader
     */
    public function __construct(
        Client\ApiClientInterface $apiClient = null,
        ConfigurationLoaderInterface $configLoader = null
    ) {
        if ($apiClient === null) {
            $apiClient = new Client\CurlClient();
        }

        if ($configLoader === null) {
            $configLoader = new ConfigurationLoader();
            $config       = $configLoader->load()->getConfig();
            $this->setConfig($config);
            $apiClient->setConfig($config);
        }
        $this->attempts  = self::DEFAULT_ATTEMPTS_COUNT;
        $this->apiClient = $apiClient;
    }

    /**
     * @param $login
     * @param $password
     *
     * @return Client $this
     */
    public function setAuth($login, $password)
    {
        $this->login    = $login;
        $this->password = $password;

        $this->apiClient
            ->setShopId($this->login)
            ->setShopPassword($this->password);

        return $this;
    }

    /**
     * @return Client\ApiClientInterface
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @param Client\ApiClientInterface $apiClient
     *
     * @return Client
     */
    public function setApiClient(Client\ApiClientInterface $apiClient)
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
     * Доступные способы оплаты.
     * Используйте этот метод, чтобы получить способы оплаты и сценарии, доступные для вашего заказа.
     *
     * @param PaymentOptionsRequestInterface|array $paymentOptionsRequest
     *
     * @return PaymentOptionsResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function getPaymentOptions($paymentOptionsRequest = null)
    {
        $path = "/payment_options";

        if ($paymentOptionsRequest === null) {
            $queryParams = array();
        } else {
            if (is_array($paymentOptionsRequest)) {
                $paymentOptionsRequest = PaymentOptionsRequest::builder()->build($paymentOptionsRequest);
            }
            $serializer  = new PaymentOptionsRequestSerializer();
            $queryParams = $serializer->serialize($paymentOptionsRequest);
        }

        $response = $this->execute($path, HttpVerb::GET, $queryParams);

        $result = null;
        if ($response->getCode() == 200) {
            $responseArray = $this->decodeData($response);
            $result        = new PaymentOptionsResponse($responseArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Получить список платежей магазина.
     *
     * @param PaymentsRequestInterface|array|null $filter
     *
     * @return PaymentsResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function getPayments($filter = null)
    {
        $path = self::PAYMENTS_PATH;

        if ($filter === null) {
            $queryParams = array();
        } else {
            if (is_array($filter)) {
                $filter = PaymentsRequest::builder()->build($filter);
            }
            $serializer  = new PaymentsRequestSerializer();
            $queryParams = $serializer->serialize($filter);
        }

        $response = $this->execute($path, HttpVerb::GET, $queryParams);

        $paymentResponse = null;
        if ($response->getCode() == 200) {
            $responseArray   = $this->decodeData($response);
            $paymentResponse = new PaymentsResponse($responseArray);
        } else {
            $this->handleError($response);
        }

        return $paymentResponse;
    }

    /**
     * Создание платежа.
     *
     * Чтобы принять оплату, необходимо создать объект платежа — `Payment`. Он содержит всю необходимую информацию
     * для проведения оплаты (сумму, валюту и статус). У платежа линейный жизненный цикл, он последовательно
     * переходит из статуса в статус.
     *
     * Необходимо указать один из параметров:
     * <ul>
     * <li>payment_token — оплата по одноразовому PaymentToken, сформированному виджетом Yandex.Checkout JS;</li>
     * <li>payment_method_id — оплата по сохраненным платежным данным;</li>
     * <li>payment_method_data — оплата по новым платежным данным.</li>
     * </ul>
     *
     * Если не указан ни один параметр и `confirmation.type = redirect`, то в качестве `confirmation_url`
     * возвращается ссылка, по которой пользователь сможет самостоятельно выбрать подходящий способ оплаты.
     * Дополнительные параметры:
     * <ul>
     * <li>confirmation — передается, если необходимо уточнить способ подтверждения платежа;</li>
     * <li>recipient — указывается при наличии нескольких товаров;</li>
     * <li>metadata — дополнительные данные (передаются магазином).</li>
     * </ul>
     *
     * @param CreatePaymentRequestInterface|array $payment
     * @param string $idempotencyKey {@link https://kassa.yandex.ru/docs/checkout-api/?php#idempotentnost}
     *
     * @return CreatePaymentResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws \Exception
     */
    public function createPayment($payment, $idempotencyKey = null)
    {
        $path = self::PAYMENTS_PATH;

        $headers = array();

        if ($idempotencyKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotencyKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }
        if (is_array($payment)) {
            $payment = CreatePaymentRequest::builder()->build($payment);
        }

        $serializer     = new CreatePaymentRequestSerializer();
        $serializedData = $serializer->serialize($payment);
        $httpBody       = $this->encodeData($serializedData);

        $response = $this->execute($path, HttpVerb::POST, null, $httpBody, $headers);

        $paymentResponse = null;
        if ($response->getCode() == 200) {
            $resultArray     = $this->decodeData($response);
            $paymentResponse = new CreatePaymentResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $paymentResponse;
    }

    /**
     * Получить информацию о платеже
     *
     * Выдает объект платежа {@link PaymentInterface} по его уникальному идентификатору.
     *
     * @param string $paymentId
     *
     * @return PaymentInterface
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function getPaymentInfo($paymentId)
    {
        if ($paymentId === null) {
            throw new \InvalidArgumentException('Missing the required parameter $paymentId');
        } elseif (!TypeCast::canCastToString($paymentId)) {
            throw new \InvalidArgumentException('Invalid paymentId value: string required');
        } elseif (strlen($paymentId) !== 36) {
            throw new \InvalidArgumentException('Invalid paymentId value');
        }

        $path = self::PAYMENTS_PATH.'/'.$paymentId;

        $response = $this->execute($path, HttpVerb::GET, null);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new PaymentResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Подтверждение платежа
     *
     * Подтверждает вашу готовность принять платеж. Платеж можно подтвердить, только если он находится
     * в статусе `waiting_for_capture`. Если платеж подтвержден успешно — значит, оплата прошла, и вы можете выдать
     * товар или оказать услугу пользователю. На следующий день после подтверждения платеж попадет в реестр,
     * и Яндекс.Касса переведет деньги на ваш расчетный счет. Если вы не подтверждаете платеж до момента, указанного
     * в `expire_at`, по умолчанию он отменяется, а деньги возвращаются пользователю. При оплате банковской картой
     * у вас есть 7 дней на подтверждение платежа. Для остальных способов оплаты платеж необходимо подтвердить
     * в течение 6 часов.
     *
     * @param CreateCaptureRequestInterface|array $captureRequest
     * @param $paymentId
     * @param $idempotencyKey {@link https://kassa.yandex.ru/docs/checkout-api/?php#idempotentnost}
     *
     * @return CreateCaptureResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws \Exception
     */
    public function capturePayment($captureRequest, $paymentId, $idempotencyKey = null)
    {
        if ($paymentId === null) {
            throw new \InvalidArgumentException('Missing the required parameter $paymentId');
        } elseif (!TypeCast::canCastToString($paymentId)) {
            throw new \InvalidArgumentException('Invalid paymentId value: string required');
        } elseif (strlen($paymentId) !== 36) {
            throw new \InvalidArgumentException('Invalid paymentId value');
        }

        $path = '/payments/'.$paymentId.'/capture';

        $headers = array();

        if ($idempotencyKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotencyKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }
        if (is_array($captureRequest)) {
            $captureRequest = CreateCaptureRequest::builder()->build($captureRequest);
        }

        $serializer     = new CreateCaptureRequestSerializer();
        $serializedData = $serializer->serialize($captureRequest);
        $httpBody       = $this->encodeData($serializedData);

        $response = $this->execute($path, HttpVerb::POST, null, $httpBody, $headers);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new CreateCaptureResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Отменить незавершенную оплату заказа.
     *
     * Отменяет платеж, находящийся в статусе `waiting_for_capture`. Отмена платежа значит, что вы
     * не готовы выдать пользователю товар или оказать услугу. Как только вы отменяете платеж, мы начинаем
     * возвращать деньги на счет плательщика. Для платежей банковскими картами отмена происходит мгновенно.
     * Для остальных способов оплаты возврат может занимать до нескольких дней.
     *
     * @param $paymentId
     * @param $idempotencyKey {@link https://kassa.yandex.ru/docs/checkout-api/?php#idempotentnost}
     *
     * @return CancelResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws \Exception
     */
    public function cancelPayment($paymentId, $idempotencyKey = null)
    {
        if ($paymentId === null) {
            throw new \InvalidArgumentException('Missing the required parameter $paymentId');
        } elseif (!TypeCast::canCastToString($paymentId)) {
            throw new \InvalidArgumentException('Invalid paymentId value: string required');
        } elseif (strlen($paymentId) !== 36) {
            throw new \InvalidArgumentException('Invalid paymentId value');
        }

        $path    = self::PAYMENTS_PATH.'/'.$paymentId.'/cancel';
        $headers = array();
        if ($idempotencyKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotencyKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }

        $response = $this->execute($path, HttpVerb::POST, null, null, $headers);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new CancelResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Получить список возвратов платежей
     *
     * @param RefundsRequestInterface|array|null $filter
     *
     * @return RefundsResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function getRefunds($filter = null)
    {
        $path = self::REFUNDS_PATH;

        if ($filter === null) {
            $queryParams = array();
        } else {
            if (is_array($filter)) {
                $filter = RefundsRequest::builder()->build($filter);
            }
            $serializer  = new RefundsRequestSerializer();
            $queryParams = $serializer->serialize($filter);
        }

        $response = $this->execute($path, HttpVerb::GET, $queryParams);

        $refundsResponse = null;
        if ($response->getCode() == 200) {
            $resultArray     = $this->decodeData($response);
            $refundsResponse = new RefundsResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $refundsResponse;
    }

    /**
     * Проведение возврата платежа
     *
     * Создает объект возврата — `Refund`. Возвращает успешно завершенный платеж по уникальному идентификатору
     * этого платежа. Создание возврата возможно только для платежей в статусе `succeeded`. Комиссии за проведение
     * возврата нет. Комиссия, которую Яндекс.Касса берёт за проведение исходного платежа, не возвращается.
     *
     * @param CreateRefundRequestInterface|array $request
     * @param null $idempotencyKey {@link https://kassa.yandex.ru/docs/checkout-api/?php#idempotentnost}
     *
     * @return CreateRefundResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws \Exception
     */
    public function createRefund($request, $idempotencyKey = null)
    {
        $path = self::REFUNDS_PATH;

        $headers = array();

        if ($idempotencyKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotencyKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }
        if (is_array($request)) {
            $request = CreateRefundRequest::builder()->build($request);
        }

        $serializer     = new CreateRefundRequestSerializer();
        $serializedData = $serializer->serialize($request);
        $httpBody       = $this->encodeData($serializedData);

        $response = $this->execute($path, HttpVerb::POST, null, $httpBody, $headers);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new CreateRefundResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Получить информацию о возврате
     *
     * @param $refundId
     *
     * @return RefundResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function getRefundInfo($refundId)
    {
        if ($refundId === null) {
            throw new \InvalidArgumentException('Missing the required parameter $refundId');
        } elseif (!TypeCast::canCastToString($refundId)) {
            throw new \InvalidArgumentException('Invalid refundId value: string required');
        } elseif (strlen($refundId) !== 36) {
            throw new \InvalidArgumentException('Invalid refundId value');
        }
        $path = self::REFUNDS_PATH.'/'.$refundId;

        $response = $this->execute($path, HttpVerb::GET, null);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new RefundResponse($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
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
     * @return Client
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
     * @return Client
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
     * @throws \Exception
     */
    private function encodeData($serializedData)
    {
        $result = json_encode($serializedData);
        if ($result === false) {
            $errorCode = json_last_error();
            throw new JsonException("Failed serialize json.", $errorCode);
        }

        return $result;
    }

    /**
     * @param ResponseObject $response
     *
     * @return array
     */
    private function decodeData(ResponseObject $response)
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
    private function handleError(ResponseObject $response)
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
     * @return ResponseObject
     * @throws Common\Exceptions\AuthorizeException
     */
    private function execute($path, $method, $queryParams, $httpBody = null, $headers = array())
    {
        $attempts = $this->attempts;
        $response = $this->apiClient->call($path, $method, $queryParams, $httpBody, $headers);

        while ($response->getCode() == 202 && $attempts > 0) {
            $this->delay($response);
            $attempts--;
            $response = $this->apiClient->call($path, $method, $queryParams, $httpBody, $headers);
        }

        return $response;
    }
}