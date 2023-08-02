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
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Информация о плательщике
 *
 * @property string $fullName Для юрлица — название организации, для ИП и физического лица — ФИО.
 * @property string $full_name Для юрлица — название организации, для ИП и физического лица — ФИО.
 * @property string $phone Номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек.
 * @property string $email E-mail адрес плательщика на который будет выслан чек.
 * @property string $inn ИНН плательщика (10 или 12 цифр).
 */
class ReceiptCustomer extends AbstractObject implements ReceiptCustomerInterface
{
    /**
     * @var string Для юрлица — название организации, для ИП и физического лица — ФИО.
     */
    private $_fullName;

    /**
     * @var string Номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек.
     */
    private $_phone;

    /**
     * @var string E-mail адрес плательщика на который будет выслан чек.
     */
    private $_email;

    /**
     * @var string ИНН плательщика (10 или 12 цифр).
     */
    private $_inn;

    /**
     * Возвращает для юрлица — название организации, для ИП и физического лица — ФИО
     * @return string Название организации или ФИО
     */
    public function getFullName()
    {
        return $this->_fullName;
    }

    /**
     * Устанавливает Название организации или ФИО
     *
     * @param string $value Название организации или ФИО
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     */
    public function setFullName($value)
    {
        if ($value === null || $value === '') {
            $this->_fullName = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid full_name value type', 0, 'receipt.customer.full_name');
        } elseif (strlen((string)$value) > 256) {
            throw new InvalidPropertyValueException(
                'Invalid full_name value: "'.$value.'"', 0, 'receipt.customer.full_name', $value
            );
        } else {
            $this->_fullName = (string)$value;
        }
    }

    /**
     * Возвращает номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек
     *
     * @return string Номер телефона плательщика
     */
    public function getPhone()
    {
        return $this->_phone;
    }

    /**
     * Устанавливает номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек
     *
     * @param string $value Номер телефона плательщика в формате ITU-T E.164
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     */
    public function setPhone($value)
    {
        if ($value === null || $value === '') {
            $this->_phone = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid phone value type', 0, 'receipt.customer.phone');
        } else {
            $this->_phone = (string)preg_replace('/\D/', '', $value);
        }
    }

    /**
     * Возвращает адрес электронной почты на который будет выслан чек
     *
     * @return string E-mail адрес плательщика
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Устанавливает адрес электронной почты на который будет выслан чек
     *
     * @param string $value E-mail адрес плательщика
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     */
    public function setEmail($value)
    {
        if ($value === null || $value === '') {
            $this->_email = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid email value type', 0, 'receipt.customer.email');
        } else {
            $this->_email = (string)$value;
        }
    }

    /**
     * @return string
     */
    public function getInn()
    {
        return $this->_inn;
    }

    /**
     * Устанавливает ИНН плательщика
     *
     * @param string $value ИНН плательщика (10 или 12 цифр)
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     * @throws InvalidPropertyValueException Выбрасывается если ИНН не соответствует формату 10 или 12 цифр
     */
    public function setInn($value)
    {
        if ($value === null || $value === '') {
            $this->_inn = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid inn value type', 0, 'receipt.customer.inn');
        } elseif (!preg_match('/^([0-9]{10}|[0-9]{12})$/', (string)$value)) {
            throw new InvalidPropertyValueException('Invalid inn value: "'.$value.'"', 0, 'receipt.customer.inn');
        } else {
            $this->_inn = (string)$value;
        }
    }

    /**
     * Проверка на заполненность объекта
     * @return bool
     */
    public function isEmpty()
    {
        $data = $this->getFullName() . $this->getEmail() . $this->getPhone() . $this->getInn();
        return empty($data);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = array();

        $value = $this->getFullName();
        if (!empty($value)) {
            $result['full_name'] = $value;
        }
        $value = $this->getEmail();
        if (!empty($value)) {
            $result['email'] = $value;
        }
        $value = $this->getPhone();
        if (!empty($value)) {
            $result['phone'] = $value;
        }
        $value = $this->getInn();
        if (!empty($value)) {
            $result['inn'] = $value;
        }

        return $result;
    }

}
