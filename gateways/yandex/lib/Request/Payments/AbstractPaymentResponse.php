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

namespace YandexCheckout\Request\Payments;

use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\AuthorizationDetails;
use YandexCheckout\Model\CancellationDetails;
use YandexCheckout\Model\Confirmation\ConfirmationRedirect;
use YandexCheckout\Model\Confirmation\ConfirmationExternal;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\Metadata;
use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\Payment;
use YandexCheckout\Model\PaymentInterface;
use YandexCheckout\Model\PaymentMethod\AbstractPaymentMethod;
use YandexCheckout\Model\PaymentMethod\PaymentMethodFactory;
use YandexCheckout\Model\Recipient;

/**
 * Абстрактный класс ответа от API, возвращающего информацию о платеже
 *
 * @package YandexCheckout\Request\Payments
 */
abstract class AbstractPaymentResponse extends Payment implements PaymentInterface
{
    /**
     * Конструктор, устанавливает настройки платежа из ассоциативного массива
     *
     * @param array $paymentInfo Массив с информацией о платеже, пришедший от API
     */
    public function __construct($paymentInfo)
    {
        $this->setId($paymentInfo['id']);
        $this->setStatus($paymentInfo['status']);
        $this->setAmount($this->factoryAmount($paymentInfo['amount']));
        $this->setCreatedAt($paymentInfo['created_at']);
        $this->setPaid($paymentInfo['paid']);
        if (!empty($paymentInfo['payment_method'])) {
            $this->setPaymentMethod($this->factoryPaymentMethod($paymentInfo['payment_method']));
        }

        if (!empty($paymentInfo['description'])) {
            $this->setDescription($paymentInfo['description']);
        }

        if (!empty($paymentInfo['recipient'])) {
            $recipient = new Recipient();
            $recipient->setAccountId($paymentInfo['recipient']['account_id']);
            $recipient->setGatewayId($paymentInfo['recipient']['gateway_id']);
            $this->setRecipient($recipient);
        }
        if (!empty($paymentInfo['captured_at'])) {
            $this->setCapturedAt(strtotime($paymentInfo['captured_at']));
        }
        if (!empty($paymentInfo['expires_at'])) {
            $this->setExpiresAt($paymentInfo['expires_at']);
        }
        if (!empty($paymentInfo['confirmation'])) {
            if ($paymentInfo['confirmation']['type'] === ConfirmationType::REDIRECT) {
                $confirmation = new ConfirmationRedirect();
                $confirmation->setConfirmationUrl($paymentInfo['confirmation']['confirmation_url']);
                if (empty($paymentInfo['confirmation']['enforce'])) {
                    $confirmation->setEnforce(false);
                } else {
                    $confirmation->setEnforce($paymentInfo['confirmation']['enforce']);
                }
                if (!empty($paymentInfo['confirmation']['return_url'])) {
                    $confirmation->setReturnUrl($paymentInfo['confirmation']['return_url']);
                }
            } else {
                $confirmation = new ConfirmationExternal();
            }
            $this->setConfirmation($confirmation);
        }
        if (!empty($paymentInfo['refunded_amount'])) {
            $this->setRefundedAmount($this->factoryAmount($paymentInfo['refunded_amount']));
        }
        if (!empty($paymentInfo['receipt_registration'])) {
            $this->setReceiptRegistration($paymentInfo['receipt_registration']);
        }
        if (!empty($paymentInfo['metadata'])) {
            $metadata = new Metadata();
            foreach ($paymentInfo['metadata'] as $key => $value) {
                $metadata->offsetSet($key, $value);
            }
            $this->setMetadata($metadata);
        }
        if (!empty($paymentInfo['cancellation_details'])) {
            $this->setCancellationDetails(new CancellationDetails(
                $paymentInfo['cancellation_details']['party'], $paymentInfo['cancellation_details']['reason']
            ));
        }
        if (!empty($paymentInfo['authorization_details'])) {
            $this->setAuthorizationDetails(new AuthorizationDetails(
                $paymentInfo['authorization_details']['rrn'], $paymentInfo['authorization_details']['auth_code']
            ));
        }
    }

    /**
     * Фабричный метод для создания способа оплаты
     *
     * @param array $options Настройки способа оплаты в массиве
     *
     * @return AbstractPaymentMethod Инстанс способа оплаты нужного типа
     */
    private function factoryPaymentMethod($options)
    {
        $factory = new PaymentMethodFactory();

        return $factory->factoryFromArray($options);
    }

    /**
     * Фабричный метод создания суммы
     *
     * @param array $options Сумма в виде ассоциативного массива
     *
     * @return AmountInterface Созданный инстанс суммы
     */
    private function factoryAmount($options)
    {
        $amount = new MonetaryAmount(null, $options['currency']);
        if ($options['value'] > 0) {
            $amount->setValue($options['value']);
        }

        return $amount;
    }
}