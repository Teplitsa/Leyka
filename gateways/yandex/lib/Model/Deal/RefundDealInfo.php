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

namespace YooKassa\Model\Deal;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Model\SettlementInterface;

/**
 * Class PaymentDealInfo
 *
 * @package YooKassa
 *
 * @property string $id Идентификатор сделки
 * @property SettlementPayoutRefund[] $refund_settlements Данные о распределении денег
 * @property SettlementPayoutRefund[] $refundSettlements Данные о распределении денег
 */
class RefundDealInfo extends AbstractObject
{
    /** @var string Идентификатор сделки */
    private $_id;

    /** @var SettlementPayoutRefund[] Данные о распределении денег */
    private $_refund_settlements = array();

    /**
     * Возвращает Id сделки
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает Id сделки
     *
     * @param string $value Id сделки
     * @return RefundDealInfo
     */
    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара
     *
     * @return SettlementInterface[] Массив оплат, обеспечивающих выдачу товара.
     */
    public function getRefundSettlements()
    {
        return $this->_refund_settlements;
    }

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара
     *
     * @param SettlementInterface[]|array $value
     * @return RefundDealInfo
     */
    public function setRefundSettlements($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty settlements value in deal', 0, 'deal.settlements');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid settlements value type in deal', 0, 'deal.settlements', $value
            );
        }
        $this->_refund_settlements = array();
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $this->addSettlement(new SettlementPayoutRefund($val));
            } elseif ($val instanceof SettlementInterface) {
                $this->addSettlement($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid settlements value type in deal', 0, 'deal.settlements['.$key.']', $val
                );
            }
        }
        return $this;
    }

    /**
     * Добавляет оплату в чек
     *
     * @param SettlementInterface $value Объект добавляемой в чек позиции
     * @return RefundDealInfo
     */
    public function addSettlement($value)
    {
        $this->_refund_settlements[] = $value;
        return $this;
    }
}
