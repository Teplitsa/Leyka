<?php

/**
 * The MIT License
 *
 * Copyright (c) 2022 "YooMoney", NBСO LLC
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

namespace YooKassa\Request\Payouts;

use YooKassa\Model\AmountInterface;
use YooKassa\Model\DealInterface;
use YooKassa\Model\Metadata;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\Deal\PayoutDealInfo;
use YooKassa\Model\Payout\AbstractPayoutDestination;

/**
 * Interface CreatePayoutRequestInterface
 *
 * @package YooKassa
 *
 * @property AmountInterface $amount Сумма создаваемой выплаты
 * @property AbstractPayoutDestination $payoutDestinationData Данные платежного средства, на которое нужно сделать выплату. Обязательный параметр, если не передан payout_token.
 * @property AbstractPayoutDestination $payout_destination_data Данные платежного средства, на которое нужно сделать выплату. Обязательный параметр, если не передан payout_token.
 * @property string $payoutToken Токенизированные данные для выплаты. Например, синоним банковской карты. Обязательный параметр, если не передан payout_destination_data
 * @property string $payout_token Токенизированные данные для выплаты. Например, синоним банковской карты. Обязательный параметр, если не передан payout_destination_data
 * @property DealInterface $deal Сделка, в рамках которой нужно провести выплату. Необходимо передавать, если вы проводите Безопасную сделку
 * @property string $description Описание транзакции (не более 128 символов). Например: «Выплата по договору N»
 * @property Metadata $metadata Метаданные привязанные к выплате
 */
interface CreatePayoutRequestInterface
{
    /**
     * Возвращает сумму выплаты
     * @return AmountInterface|MonetaryAmount Сумма выплаты
     */
    function getAmount();

    /**
     * Проверяет наличие суммы в создаваемой выплате
     * @return bool True если сумма установлена, false если нет
     */
    function hasAmount();

    /**
     * Устанавливает сумму выплаты
     * @param AmountInterface|array Сумма выплаты
     */
    function setAmount($value);

    /**
     * Возвращает данные платежного средства, на которое нужно сделать выплату
     * @return AbstractPayoutDestination|null Данные платежного средства, на которое нужно сделать выплату
     */
    function getPayoutDestinationData();

    /**
     * Проверяет наличие данных платежного средства, на которое нужно сделать выплату
     * @return bool True если данные есть, false если нет
     */
    function hasPayoutDestinationData();

    /**
     * Устанавливает данные платежного средства, на которое нужно сделать выплату
     * @param AbstractPayoutDestination|array|null $value Данные платежного средства, на которое нужно сделать выплату
     */
    function setPayoutDestinationData($value);

    /**
     * Возвращает токенизированные данные для выплаты
     * @return string Токенизированные данные для выплаты
     */
    function getPayoutToken();

    /**
     * Проверяет наличие токенизированных данных для выплаты
     * @return bool True если токен установлен, false если нет
     */
    function hasPayoutToken();

    /**
     * Устанавливает токенизированные данные для выплаты
     * @param string $value Токенизированные данные для выплаты
     */
    function setPayoutToken($value);

    /**
     * Возвращает описание транзакции
     * @return string Описание транзакции
     */
    function getDescription();

    /**
     * Проверяет наличие описания транзакции в создаваемой выплате
     * @return bool True если описание транзакции установлено, false если нет
     */
    function hasDescription();

    /**
     * Устанавливает описание транзакции
     * @param string $value Описание транзакции
     */
    function setDescription($value);

    /**
     * Возвращает сделку, в рамках которой нужно провести выплату
     * @return PayoutDealInfo|null Сделка, в рамках которой нужно провести выплату
     */
    function getDeal();

    /**
     * Проверяет установлена ли сделка, в рамках которой нужно провести выплату
     * @return bool True если сделка установлена, false если нет
     */
    function hasDeal();

    /**
     * Устанавливает сделку, в рамках которой нужно провести выплату
     * @param PayoutDealInfo|array|null $value Сделка, в рамках которой нужно провести выплату
     */
    function setDeal($value);

    /**
     * Возвращает данные оплаты установленные мерчантом
     * @return Metadata Метаданные привязанные к выплате
     */
    function getMetadata();

    /**
     * Проверяет, были ли установлены метаданные заказа
     * @return bool True если метаданные были установлены, false если нет
     */
    function hasMetadata();

    /**
     * Устанавливает метаданные, привязанные к выплате
     * @param Metadata|array|null $value Метаданные платежа, устанавливаемые мерчантом
     */
    function setMetadata($value);
}
