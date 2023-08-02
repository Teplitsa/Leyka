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

namespace YooKassa\Model;

/**
 * Interface AuthorizationDetailsInterface - Данные об авторизации платежа
 *
 * @package YooKassa
 *
 * @property-read string $rrn Retrieval Reference Number — уникальный идентификатор транзакции в системе эмитента
 * @property-read string $authCode Код авторизации банковской карты
 * @property-read ThreeDSecure $threeDSecure Данные о прохождении пользователем аутентификации по 3‑D Secure
 */
interface AuthorizationDetailsInterface
{
    /**
     * Возвращает Retrieval Reference Number — уникальный идентификатор транзакции в системе эмитента
     * @return string|null Уникальный идентификатор транзакции
     */
    function getRrn();

    /**
     * Возвращает код авторизации банковской карты
     * @return string|null Код авторизации банковской карты
     */
    function getAuthCode();

    /**
     * Возвращает данные о прохождении пользователем аутентификации по 3‑D Secure
     * @return ThreeDSecure|null Объект с данными о прохождении пользователем аутентификации по 3‑D Secure
     */
    function getThreeDSecure();
}
