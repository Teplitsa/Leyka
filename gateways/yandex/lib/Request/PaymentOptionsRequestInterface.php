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

namespace YandexCheckout\Request;

/**
 * Интерфейс объекта запроса на получение списка возможных способов оплаты от API
 * 
 * @package YandexCheckout\Request
 * 
 * @property-read string $accountId Идентификатор магазина
 * @property-read string $gatewayId Идентификатор шлюза
 * @property-read string $amount Сумма заказа
 * @property-read string $currency Код валюты
 * @property-read string $confirmationType Сценарий подтверждения платежа
 */
interface PaymentOptionsRequestInterface
{
    /**
     * Возвращает идентификатор магазина для которого требуется провести платёж
     * @return string Идентификатор магазина
     */
    function getAccountId();

    /**
     * Проверяет был ли установлен идентификатор магазина
     * @return bool True если идентификатор магазина был установлен, false если нет
     */
    function hasAccountId();

    /**
     * Возвращает идентификатор шлюза
     * @return string|null Идентификатор шлюза
     */
    function getGatewayId();

    /**
     * Проверяет был ли установлен идентификатор шлюза
     * @return bool True если идентификатор шлюза был установлен, false если нет
     */
    function hasGatewayId();

    /**
     * Возвращает сумму заказа
     * @return string Сумма заказа
     */
    function getAmount();

    /**
     * Проверяет была ли установлена сумма заказа
     * @return bool True если сумма заказа была установлена, false если нет
     */
    function hasAmount();

    /**
     * Возвращает код валюты, в которой осуществляется покупка
     * @return string Код валюты
     */
    function getCurrency();

    /**
     * Проверяет был ли установлен код валюты
     * @return bool True если код валюты был установлен, false если нет
     */
    function hasCurrency();

    /**
     * Возвращает сценарий подтверждения платежа, для которого запрашивается список способов оплаты
     * @return string Сценарий подтверждения платежа
     */
    function getConfirmationType();

    /**
     * Проверяет был ли установлен способ подтверждения платежа
     * @return bool True если способ подтверждения платежа был установлен, false если нет
     */
    function hasConfirmationType();
}