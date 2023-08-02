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

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * AuthorizationDetails - Данные об авторизации платежа
 *
 * @property $rrn Retrieval Reference Number — уникальный идентификатор транзакции в системе эмитента
 * @property string $authCode Код авторизации банковской карты
 * @property ThreeDSecure $threeDSecure Данные о прохождении пользователем аутентификации по 3‑D Secure
 */
class AuthorizationDetails extends AbstractObject implements AuthorizationDetailsInterface
{
    /**
     * @var string Уникальный идентификатор транзакции
     */
    private $_rrn = '';

    /**
     * @var string Код авторизации банковской карты
     */
    private $_authCode = '';

    /**
     * @var ThreeDSecure Данные о прохождении пользователем аутентификации по 3‑D Secure
     */
    private $_threeDSecure;

    public function fromArray($sourceArray)
    {

        if (isset($sourceArray['rrn'])) {
            $this->setRrn($sourceArray['rrn']);
        }

        if (isset($sourceArray['auth_code'])) {
            $this->setAuthCode($sourceArray['auth_code']);
        }


        if (isset($sourceArray['three_d_secure'])) {
            $this->setThreeDSecure($sourceArray['three_d_secure']);
        }
    }

    /**
     * Возвращает уникальный идентификатор транзакции
     *
     * @return string|null Уникальный идентификатор транзакции
     */
    public function getRrn()
    {
        return $this->_rrn;
    }

    /**
     * Возвращает код авторизации банковской карты
     *
     * @return string|null Код авторизации банковской карты
     */
    public function getAuthCode()
    {
        return $this->_authCode;
    }

    /**
     * Возвращает данные о прохождении пользователем аутентификации по 3‑D Secure
     *
     * @return ThreeDSecure|null Объект с данными о прохождении пользователем аутентификации по 3‑D Secure
     */
    public function getThreeDSecure()
    {
        return $this->_threeDSecure;
    }

    /**
     * Устанавливает уникальный идентификатор транзакции
     *
     * @param $value
     *
     * @throws InvalidPropertyValueTypeException
     */
    public function setRrn($value)
    {
        if ($value === null || $value === '') {
            $this->_rrn = $value;
        } elseif (TypeCast::canCastToEnumString($value)) {
            $this->_rrn = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid rrn value type', 0,
                'authorization_details.rrn', $value);
        }
    }

    /**
     * Устанавливает код авторизации банковской карты
     *
     * @param $value
     *
     * @throws InvalidPropertyValueTypeException
     */
    public function setAuthCode($value)
    {
        if ($value === null || $value === '') {
            $this->_authCode = $value;
        } elseif (TypeCast::canCastToEnumString($value)) {
            $this->_authCode = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid auth_code value type', 0,
                'authorization_details.auth_code', $value);
        }
    }

    /**
     * Устанавливает данные о прохождении пользователем аутентификации по 3‑D Secure
     *
     * @param ThreeDSecure|array $value Данные о прохождении аутентификации по 3‑D Secure
     *
     * @throws InvalidPropertyValueTypeException
     */
    public function setThreeDSecure($value)
    {
        if (is_array($value)) {
            $this->_threeDSecure = new ThreeDSecure($value);
        } elseif ($value instanceof ThreeDSecure) {
            $this->_threeDSecure = $value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid three_d_secure value type', 0,
                'authorization_details.three_d_secure', $value);
        }
    }
}
