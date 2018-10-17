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

use YandexCheckout\Model\Airline;
use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\ConfirmationAttributes\AbstractConfirmationAttributes;
use YandexCheckout\Model\Metadata;
use YandexCheckout\Model\PaymentData\AbstractPaymentData;
use YandexCheckout\Model\ReceiptInterface;
use YandexCheckout\Model\RecipientInterface;

/**
 * Interface CreatePaymentRequestInterface
 *
 * @package YandexCheckout\Request\Payments
 *
 * @property-read RecipientInterface|null $recipient Получатель платежа, если задан
 * @property-read AmountInterface $amount Сумма создаваемого платежа
 * @property-read ReceiptInterface $receipt Данные фискального чека 54-ФЗ
 * @property-read string $paymentToken Одноразовый токен для проведения оплаты, сформированный
 * Yandex.Checkout JS widget
 * @property-read string $payment_token Одноразовый токен для проведения оплаты, сформированный
 * Yandex.Checkout JS widget
 * @property-read string $paymentMethodId Идентификатор записи о сохраненных платежных данных покупателя
 * @property-read string $payment_method_id Идентификатор записи о сохраненных платежных данных покупателя
 * @property-read AbstractPaymentData $paymentMethodData Данные используемые для создания метода оплаты
 * @property-read AbstractPaymentData $payment_method_data Данные используемые для создания метода оплаты
 * @property-read AbstractConfirmationAttributes $confirmation Способ подтверждения платежа
 * @property-read bool $savePaymentMethod Сохранить платежные данные для последующего использования
 * @property-read bool $save_payment_method Сохранить платежные данные для последующего использования
 * @property-read bool $capture Автоматически принять поступившую оплату
 * @property-read string $clientIp IPv4 или IPv6-адрес покупателя. Если не указан, используется IP-адрес
 * TCP-подключения.
 * @property-read string $client_ip IPv4 или IPv6-адрес покупателя. Если не указан, используется IP-адрес
 * TCP-подключения.
 * @property-read Metadata $metadata Метаданные привязанные к платежу
 */
interface CreatePaymentRequestInterface
{
    /**
     * Возвращает объект получателя платежа
     * @return RecipientInterface|null Объект с информацией о получателе платежа или null если получатель не задан
     */
    function getRecipient();

    /**
     * Проверяет наличие получателя платежа в запросе
     * @return bool True если получатель платежа задан, false если нет
     */
    function hasRecipient();

    /**
     * Возвращает сумму заказа
     * @return AmountInterface Сумма заказа
     */
    function getAmount();

    /**
     * Возвращает описание транзакции
     * @return string Описание транзакции
     */
    function getDescription();

    /**
     * Проверяет наличие описания транзакции в создаваемом платеже
     * @return bool True если описание транзакции установлено, false если нет
     */
    function hasDescription();

    /**
     * Возвращает чек, если он есть
     * @return ReceiptInterface|null Данные фискального чека 54-ФЗ или null если чека нет
     */
    function getReceipt();

    /**
     * Проверяет наличие чека в создаваемом платеже
     * @return bool True если чек есть, false если нет
     */
    function hasReceipt();

    /**
     * Возвращает одноразовый токен для проведения оплаты
     * @return string Одноразовый токен для проведения оплаты, сформированный Yandex.Checkout JS widget
     */
    function getPaymentToken();

    /**
     * Проверяет наличие одноразового токена для проведения оплаты
     * @return bool True если токен установлен, false если нет
     */
    function hasPaymentToken();

    /**
     * Устанавливает идентификатор закиси платёжных данных покупателя
     * @return string Идентификатор записи о сохраненных платежных данных покупателя
     */
    function getPaymentMethodId();

    /**
     * Проверяет наличие идентификатора записи о платёжных данных покупателя
     * @return bool True если идентификатор задан, false если нет
     */
    function hasPaymentMethodId();

    /**
     * Возвращает данные для создания метода оплаты
     * @return AbstractPaymentData Данные используемые для создания метода оплаты
     */
    function getPaymentMethodData();

    /**
     * Проверяет установлен ли объект с методом оплаты
     * @return bool True если объект метода оплаты установлен, false если нет
     */
    function hasPaymentMethodData();

    /**
     * Возвращает способ подтверждения платежа
     * @return AbstractConfirmationAttributes Способ подтверждения платежа
     */
    function getConfirmation();

    /**
     * Проверяет был ли установлен способ подтверждения платежа
     * @return bool True если способ подтверждения платежа был установлен, false если нет
     */
    function hasConfirmation();

    /**
     * Возвращает флаг сохранения платёжных данных
     * @return bool Флаг сохранения платёжных данных
     */
    function getSavePaymentMethod();

    /**
     * Проверяет был ли установлен флаг сохранения платёжных данных
     * @return bool True если флыг был установлен, false если нет
     */
    function hasSavePaymentMethod();

    /**
     * Возвращает флаг автоматического принятия поступившей оплаты
     * @return bool True если требуется автоматически принять поступившую оплату, false если нет
     */
    function getCapture();

    /**
     * Проверяет был ли установлен флаг автоматического приняти поступившей оплаты
     * @return bool True если флаг автоматического принятия оплаты был установлен, false если нет
     */
    function hasCapture();

    /**
     * Возвращает IPv4 или IPv6-адрес покупателя
     * @return string IPv4 или IPv6-адрес покупателя
     */
    function getClientIp();

    /**
     * Проверяет был ли установлен IPv4 или IPv6-адрес покупателя
     * @return bool True если IP адрес покупателя был установлен, false если нет
     */
    function hasClientIp();

    /**
     * Возвращает данные оплаты установленные мерчантом
     * @return Metadata Метаданные привязанные к платежу
     */
    function getMetadata();

    /**
     * Проверяет были ли установлены метаданные заказа
     * @return bool True если метаданные были установлены, false если нет
     */
    function hasMetadata();

    /**
     * Проверяет были ли установлены данные длинной записи
     * @return bool
     */
    function hasAirline();

    /**
     * Возвращает данные длинной записи
     * @return Airline
     */
    function getAirline();
}
