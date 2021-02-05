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
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Информация о поставщике товара или услуги.
 *
 * @package YooKassa\Model
 */
class Supplier extends AbstractObject implements SupplierInterface
{
    /** @var string Наименование поставщика. */
    private $_name;

    /** @var string Телефон пользователя. Указывается в формате ITU-T E.164 */
    private $_phone;

    /** @var string ИНН пользователя (10 или 12 цифр) */
    private $_inn;

    /**
     * Supplier constructor.
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (!empty($data) && is_array($data)) {
            $this->fromArray($data);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $value Наименование поставщика
     */
    public function setName($value)
    {
        if ($value === null || $value === '') {
            $this->_name = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid name value type', 0, 'receipt.supplier.name');
        } else {
            $this->_name = (string)$value;
        }
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->_phone;
    }

    /**
     * @param string $value Номер телефона пользователя в формате ITU-T E.164
     */
    public function setPhone($value)
    {
        if ($value === null || $value === '') {
            $this->_phone = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid phone value type', 0, 'receipt.supplier.phone');
        } else {
            $this->_phone = (string)$value;
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
     * @param string $value ИНН пользователя (10 или 12 цифр)
     */
    public function setInn($value)
    {
        if ($value === null || $value === '') {
            $this->_inn = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid inn value type', 0, 'receipt.supplier.inn');
        } elseif (!preg_match('/^([0-9]{10}|[0-9]{12})$/', (string)$value)) {
            throw new InvalidPropertyValueException('Invalid inn value: "'.$value.'"', 0, 'receipt.supplier.inn');
        } else {
            $this->_inn = (string)$value;
        }
    }
}