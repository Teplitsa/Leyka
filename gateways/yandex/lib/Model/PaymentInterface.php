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

namespace YandexCheckout\Model;

use YandexCheckout\Model\PaymentMethod\AbstractPaymentMethod;

/**
 * Interface PaymentInterface
 *
 * @package YandexCheckout\Model
 *
 * @property-read string $id Идентификатор платежа
 * @property-read string $status Текущее состояние платежа
 * @property-read RecipientInterface $recipient Получатель платежа
 * @property-read AmountInterface $amount Сумма заказа
 * @property-read AbstractPaymentMethod $paymentMethod Способ проведения платежа
 * @property-read AbstractPaymentMethod $payment_method Способ проведения платежа
 * @property-read \DateTime $createdAt Время создания заказа
 * @property-read \DateTime $created_at Время создания заказа
 * @property-read \DateTime $capturedAt Время подтверждения платежа магазином
 * @property-read \DateTime $captured_at Время подтверждения платежа магазином
 * @property-read Confirmation\AbstractConfirmation $confirmation Способ подтверждения платежа
 * @property-read AmountInterface $refundedAmount Сумма возвращенных средств платежа
 * @property-read AmountInterface $refunded_amount Сумма возвращенных средств платежа
 * @property-read bool $paid Признак оплаты заказа
 * @property-read string $receiptRegistration Состояние регистрации фискального чека
 * @property-read string $receipt_registration Состояние регистрации фискального чека
 * @property-read Metadata $metadata Метаданные платежа указанные мерчантом
 */
interface PaymentInterface
{
    /**
     * Возвращает идентификатор платежа
     * @return string Идентификатор платежа
     */
    function getId();

    /**
     * Возвращает состояние платежа
     * @return string Текущее состояние платежа
     */
    public function getStatus();

    /**
     * Возвращает получателя платежа
     * @return RecipientInterface|null Получатель платежа или null если получатель не задан
     */
    public function getRecipient();

    /**
     * Возвращает сумму
     * @return AmountInterface Сумма платежа
     */
    public function getAmount();

    /**
     * Возвращает используемый способ проведения платежа
     * @return AbstractPaymentMethod Способ проведения платежа
     */
    public function getPaymentMethod();

    /**
     * Возвращает время создания заказа
     * @return \DateTime Время создания заказа
     */
    public function getCreatedAt();

    /**
     * Возвращает время подтверждения платежа магазином или null если если время не задано
     * @return \DateTime|null Время подтверждения платежа магазином
     */
    public function getCapturedAt();

    /**
     * Возвращает способ подтверждения платежа
     * @return Confirmation\AbstractConfirmation Способ подтверждения платежа
     */
    public function getConfirmation();

    /**
     * Возвращает сумму возвращенных средств
     * @return AmountInterface Сумма возвращенных средств платежа
     */
    public function getRefundedAmount();

    /**
     * Проверяет был ли уже оплачен заказ
     * @return bool Признак оплаты заказа, true если заказ оплачен, false если нет
     */
    public function getPaid();

    /**
     * Возвращает состояние регистрации фискального чека
     * @return string Состояние регистрации фискального чека
     */
    public function getReceiptRegistration();

    /**
     * Возвращает метаданные платежа установленные мерчантом
     * @return Metadata Метаданные платежа указанные мерчантом
     */
    public function getMetadata();

    /**
     * Возвращает время до которого можно бесплатно отменить или подтвердить платеж или null если оно не задано
     * @return \DateTime|null Время, до которого можно бесплатно отменить или подтвердить платеж
     * @since 1.0.2
     */
    public function getExpiresAt();

    /**
     * Возвращает комментарий к статусу canceled: кто отменил платеж и по какой причине
     * @return CancellationDetailsInterface|null Комментарий к статусу canceled
     * @since 1.0.13
     */
    public function getCancellationDetails();

    /**
     * Возвращает данные об авторизации платежа
     * @return AuthorizationDetailsInterface|null Данные об авторизации платежа
     * @since 1.0.18
     */
    public function getAuthorizationDetails();
}