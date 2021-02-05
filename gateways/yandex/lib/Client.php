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

namespace YooKassa;

use Exception;
use InvalidArgumentException;
use YooKassa\Client\BaseClient;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\AuthorizeException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Common\HttpVerb;
use YooKassa\Helpers\TypeCast;
use YooKassa\Helpers\UUID;
use YooKassa\Model\PaymentInterface;
use YooKassa\Model\RefundInterface;
use YooKassa\Model\Webhook\Webhook;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Request\Payments\CreatePaymentRequestInterface;
use YooKassa\Request\Payments\CreatePaymentResponse;
use YooKassa\Request\Payments\CreatePaymentRequestSerializer;
use YooKassa\Request\Payments\Payment\CancelResponse;
use YooKassa\Request\Payments\Payment\CreateCaptureRequest;
use YooKassa\Request\Payments\Payment\CreateCaptureRequestInterface;
use YooKassa\Request\Payments\Payment\CreateCaptureRequestSerializer;
use YooKassa\Request\Payments\Payment\CreateCaptureResponse;
use YooKassa\Request\Payments\PaymentResponse;
use YooKassa\Request\Payments\PaymentsRequest;
use YooKassa\Request\Payments\PaymentsRequestInterface;
use YooKassa\Request\Payments\PaymentsRequestSerializer;
use YooKassa\Request\Payments\PaymentsResponse;
use YooKassa\Request\Receipts\AbstractReceiptResponse;
use YooKassa\Request\Receipts\CreatePostReceiptRequest;
use YooKassa\Request\Receipts\CreatePostReceiptRequestInterface;
use YooKassa\Request\Receipts\CreatePostReceiptRequestSerializer;
use YooKassa\Request\Receipts\ReceiptResponseFactory;
use YooKassa\Request\Receipts\ReceiptsRequest;
use YooKassa\Request\Receipts\ReceiptsRequestSerializer;
use YooKassa\Request\Receipts\ReceiptsResponse;
use YooKassa\Request\Refunds\CreateRefundRequest;
use YooKassa\Request\Refunds\CreateRefundRequestInterface;
use YooKassa\Request\Refunds\CreateRefundRequestSerializer;
use YooKassa\Request\Refunds\CreateRefundResponse;
use YooKassa\Request\Refunds\RefundResponse;
use YooKassa\Request\Refunds\RefundsRequest;
use YooKassa\Request\Refunds\RefundsRequestInterface;
use YooKassa\Request\Refunds\RefundsRequestSerializer;
use YooKassa\Request\Refunds\RefundsResponse;
use YooKassa\Request\Webhook\WebhookListResponse;

/**
 * Класс клиента API
 *
 * @package YooKassa
 *
 * @since 1.0.1
 */
class Client extends BaseClient
{
    /**
     * Текущая версия библиотеки
     */
    const SDK_VERSION = '2.0.5';

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
     * @throws ExtensionNotFoundException
     * @throws Exception
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
     * <li>payment_token — оплата по одноразовому PaymentToken, сформированному виджетом YooKassa JS;</li>
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
     * @param string|null $idempotenceKey {@link https://yookassa.ru/developers/using-api/basics?lang=php#idempotence}
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
     * @throws Exception
     */
    public function createPayment($payment, $idempotenceKey = null)
    {
        $path = self::PAYMENTS_PATH;

        $headers = array();

        if ($idempotenceKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotenceKey;
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
     * @throws ExtensionNotFoundException
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
     * и ЮKassa переведет деньги на ваш расчетный счет. Если вы не подтверждаете платеж до момента, указанного
     * в `expire_at`, по умолчанию он отменяется, а деньги возвращаются пользователю. При оплате банковской картой
     * у вас есть 7 дней на подтверждение платежа. Для остальных способов оплаты платеж необходимо подтвердить
     * в течение 6 часов.
     *
     * @param CreateCaptureRequestInterface|array $captureRequest
     * @param $paymentId
     * @param $idempotencyKey {@link https://yookassa.ru/developers/using-api/basics?lang=php#idempotence}
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
     * @throws Exception
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
     * @param $idempotencyKey {@link https://yookassa.ru/developers/using-api/basics?lang=php#idempotence}
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
     * @throws Exception
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
     * @throws ExtensionNotFoundException
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
     * возврата нет. Комиссия, которую ЮKassa берёт за проведение исходного платежа, не возвращается.
     *
     * @param CreateRefundRequestInterface|array $request
     * @param null $idempotencyKey {@link https://yookassa.ru/developers/using-api/basics?lang=php#idempotence}
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
     * @throws Exception
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
     * @throws ExtensionNotFoundException
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
     * Создание Webhook
     * Запрос позволяет подписаться на уведомления о событии (например, на переход платежа в статус successed).
     *
     * @param $request
     * @param null $idempotencyKey
     * @return Webhook|null
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws AuthorizeException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function addWebhook($request, $idempotencyKey = null)
    {
        $path = self::WEBHOOKS_PATH;

        $headers = array();

        if ($idempotencyKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotencyKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }
        if (is_array($request)) {
            $webhook = new Webhook($request);
        } else {
            $webhook = $request;
        }

        if (!($webhook instanceof Webhook)) {
            throw new InvalidArgumentException();
        }

        $httpBody = $this->encodeData($webhook->jsonSerialize());

        $response = $this->execute($path, HttpVerb::POST, null, $httpBody, $headers);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new Webhook($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Удаление Webhook
     * Запрос позволяет отписаться от уведомлений о событии для переданного OAuth-токена. Чтобы удалить webhook, вам нужно передать в запросе его идентификатор.
     *
     * @param $webhookId
     * @param null $idempotencyKey

     * @return Webhook|null
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws Common\Exceptions\AuthorizeException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function removeWebhook($webhookId, $idempotencyKey = null)
    {
        $headers = array();

        if ($idempotencyKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotencyKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }
        $path    = self::WEBHOOKS_PATH.'/'.$webhookId;

        $response = $this->execute($path, HttpVerb::DELETE, null, null, $headers);

        $result = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $result      = new Webhook($resultArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Список созданных Webhook
     * Запрос позволяет узнать, какие webhook есть для переданного OAuth-токена.
     *
     * @return WebhookListResponse|null
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws Common\Exceptions\AuthorizeException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws ExtensionNotFoundException
     */
    public function getWebhooks()
    {
        $path = self::WEBHOOKS_PATH;

        $response = $this->execute($path, HttpVerb::GET, null);

        $result = null;
        if ($response->getCode() == 200) {
            $responseArray = $this->decodeData($response);
            $result        = new WebhookListResponse($responseArray);
        } else {
            $this->handleError($response);
        }

        return $result;
    }

    /**
     * Получить список платежей магазина.
     *
     * @param PaymentInterface|RefundInterface|array|null $filter
     *
     * @return ReceiptsResponse
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws ExtensionNotFoundException
     * @throws Exception
     */
    public function getReceipts($filter = null)
    {
        $path = self::RECEIPTS_PATH;

        if ($filter === null) {
            $queryParams = array();
        } else {
            if (is_array($filter)) {
                $filter = ReceiptsRequest::builder()->build($filter);
            }
            $serializer  = new ReceiptsRequestSerializer();
            $queryParams = $serializer->serialize($filter);
        }

        $response = $this->execute($path, HttpVerb::GET, $queryParams);

        $receiptsResponse = null;
        if ($response->getCode() == 200) {
            $responseArray    = $this->decodeData($response);
            $receiptsResponse = new ReceiptsResponse($responseArray);
        } else {
            $this->handleError($response);
        }

        return $receiptsResponse;
    }

    /**
     * @param CreatePostReceiptRequestInterface|array $receipt
     * @param string|null $idempotenceKey
     *
     * @return AbstractReceiptResponse|null
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws Common\Exceptions\ApiConnectionException
     * @throws Common\Exceptions\AuthorizeException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function createReceipt($receipt, $idempotenceKey = null)
    {
        $path = self::RECEIPTS_PATH;

        $headers = array();

        if ($idempotenceKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotenceKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }

        if (is_array($receipt)) {
            $receipt = CreatePostReceiptRequest::builder()->build($receipt);
        }

        $serializer     = new CreatePostReceiptRequestSerializer();
        $serializedData = $serializer->serialize($receipt);
        $httpBody       = $this->encodeData($serializedData);

        $response = $this->execute($path, HttpVerb::POST, null, $httpBody, $headers);

        $receiptResponse = null;
        if ($response->getCode() == 200) {
            $resultArray = $this->decodeData($response);
            $factory = new ReceiptResponseFactory();
            $receiptResponse = $factory->factory($resultArray);
        } else {
            $this->handleError($response);
        }

        return $receiptResponse;
    }

    /**
     * Информация о магазине
     * Запрос позволяет получить информацию о магазине для переданного OAuth-токена.
     *
     * @return array|null
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws Common\Exceptions\AuthorizeException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws ExtensionNotFoundException
     */
    public function me()
    {
        $path = self::ME_PATH;

        $response = $this->execute($path, HttpVerb::GET, null);

        $result = null;
        if ($response->getCode() == 200) {
            $responseArray = $this->decodeData($response);
            $result        = $responseArray;
        } else {
            $this->handleError($response);
        }

        return $result;
    }
}