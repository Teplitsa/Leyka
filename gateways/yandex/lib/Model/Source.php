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
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Класс объекта распределения денег в магазин
 */
class Source extends AbstractObject implements SourceInterface
{
    /**
     * @var string
     */
    private $_accountId;

    /**
     * @var AmountInterface
     */
    private $_amount;

    /**
     * @var AmountInterface
     */
    private $_platform_fee_amount;

    /**
     * Transfer constructor.
     * @param array $data
     */
    public function __construct($data = null)
    {
        if (isset($data) && is_array($data)) {
            if (!empty($data['account_id'])) {
                $this->setAccountId($data['account_id']);
            }
            if (!empty($data['amount'])) {
                $this->setAmount($this->factoryAmount($data['amount']));
            }
            if (!empty($data['platform_fee_amount'])) {
                $this->setPlatformFeeAmount($this->factoryAmount($data['platform_fee_amount']));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setAccountId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } else {
            $this->_accountId = (string)$value;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * @inheritDoc
     */
    public function hasAmount()
    {
        return !empty($this->_amount);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "amount" parameter in Source', 0, 'source.amount'
            );
        } elseif (is_array($value)) {
            $this->_amount = $this->factoryAmount($value);
        } elseif ($value instanceof AmountInterface) {
            $this->_amount = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "amount" parameter in Source', 0, 'source.amount', $value
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getPlatformFeeAmount()
    {
        return $this->_platform_fee_amount;
    }

    /**
     * @inheritDoc
     */
    public function hasPlatformFeeAmount()
    {
        return !empty($this->_platform_fee_amount);
    }

    /**
     * @inheritDoc
     */
    public function setPlatformFeeAmount($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "platform_fee_amount" parameter in Source', 0, 'source.platform_fee_amount'
            );
        } elseif (is_array($value)) {
            $this->_platform_fee_amount = $this->factoryAmount($value);
        } elseif ($value instanceof AmountInterface) {
            $this->_platform_fee_amount = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "platform_fee_amount" parameter in Source', 0, 'source.platform_fee_amount', $value
            );
        }
    }

    /**
     * Фабричный метод создания суммы
     *
     * @param array $options Сумма в виде ассоциативного массива
     *
     * @return AmountInterface Созданный инстанс суммы
     */
    private function factoryAmount($options)
    {
        $amount = new MonetaryAmount(null, $options['currency']);
        if ($options['value'] > 0) {
            $amount->setValue($options['value']);
        }

        return $amount;
    }
}
