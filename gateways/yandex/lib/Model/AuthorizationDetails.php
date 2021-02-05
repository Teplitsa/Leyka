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

namespace YooKassa\Model;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * AuthorizationDetails - Данные об авторизации платежа
 *
 * @property $rrn Retrieval Reference Number — уникальный идентификатор транзакции в системе эмитента
 * @property string $authCode Код авторизации банковской карты
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
     * @param string|null $rrn Уникальный идентификатор транзакции
     * @param string|null $authCode Код авторизации банковской карты
     */
    public function __construct($rrn = null, $authCode = null)
    {
        if ($rrn !== null) {
            $this->setRrn($rrn);
        }
        if ($authCode !== null) {
            $this->setAuthCode($authCode);
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
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'rrn'       => $this->_rrn,
            'auth_code' => $this->_authCode,
        );
    }

    /**
     * Устанавливает уникальный идентификатор транзакции
     * @param $value
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
     * @param $value
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
}