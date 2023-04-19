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

namespace YooKassa\Model\PaymentMethod;

use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentMethodType;

/**
 * Класс, описывающий метод оплаты, при оплате через Сбербанк Онлайн
 *
 * @property string $type Тип объекта
 * @property string $phone Телефон пользователя
 */
class PaymentMethodSberbank extends AbstractPaymentMethod
{
    /**
     * Телефон пользователя, на который зарегистрирован аккаунт в Сбербанке Онлайн.
     *
     * Необходим для подтверждения оплаты по смс (сценарий подтверждения `external`).
     * Указывается в формате [ITU-T E.164](https://ru.wikipedia.org/wiki/E.164), например `79000000000`.
     *
     * @var string Телефон пользователя
     */
    private $_phone;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::SBERBANK);
    }

    /**
     * Возвращает номер телефона в формате ITU-T E.164
     * @return string Номер телефона в формате ITU-T E.164
     */
    public function getPhone()
    {
        return $this->_phone;
    }

    /**
     * Устанавливает номер телефона в формате ITU-T E.164
     * @param string $value Номер телефона в формате ITU-T E.164
     */
    public function setPhone($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty phone value', 0, 'PaymentMethodSberbank.phone');
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[0-9]{4,15}$/', $value)) {
                $this->_phone = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid phone value', 0, 'PaymentMethodSberbank.phone', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid phone value type', 0, 'PaymentMethodSberbank.phone', $value
            );
        }
    }
}
