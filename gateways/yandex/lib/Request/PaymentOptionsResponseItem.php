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

use YandexCheckout\Common\AbstractObject;
use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\PaymentMethodType;

/**
 * Класс способов оплаты, возвращаемых API при запросе возможных способов оплаты
 *
 * @package YandexCheckout\Request
 *
 * @property-read string $paymentMethodType Тип источника средств для проведения платежа
 * @property-read string[] $confirmationTypes Список возможных сценариев подтверждения платежа
 * @property-read AmountInterface $charge Сумма платежа
 * @property-read AmountInterface $fee Сумма комиссии
 * @property-read bool $extraFee Признак присутствия дополнительной комиссии на стороне партнера
 */
class PaymentOptionsResponseItem extends AbstractObject
{
    /**
     * @var string Тип источника средств для проведения платежа
     */
    private $_paymentMethodType;

    /**
     * @var string[] Список возможных сценариев подтверждения платежа
     */
    private $_confirmationTypes;

    /**
     * @var AmountInterface Сумма платежа
     */
    private $_charge;

    /**
     * @var AmountInterface Сумма дополнительной комиссии при проведении платежа с помощью текущего способа оплаты
     */
    private $_fee;

    /**
     * @var bool Признак присутствия дополнительной комиссии на стороне партнера
     */
    private $_extraFee;

    public function __construct($options)
    {
        $this->_paymentMethodType = $options['payment_method_type'];
        $this->_confirmationTypes = array();
        foreach ($options['confirmation_types'] as $opt) {
            $this->_confirmationTypes[] = $opt;
        }

        $this->_charge = new MonetaryAmount($options['charge']['value'], $options['charge']['currency']);
        $this->_fee = new MonetaryAmount();
        if (!empty($options['fee'])) {
            $this->_fee->setValue($options['fee']['value']);
            $this->_fee->setCurrency($options['fee']['currency']);
        } else {
            $this->_fee->setCurrency($options['charge']['currency']);
        }

        $this->_extraFee = false;
        if (!empty($options['extra_fee'])) {
            $this->_extraFee = (bool)$options['extra_fee'];
        }
    }

    /**
     * Возвращает тип источника средств для проведения платежа
     * @return string Тип источника средств для проведения платежа
     * @see PaymentMethodType
     */
    public function getPaymentMethodType()
    {
        return $this->_paymentMethodType;
    }

    /**
     * Возвращает список возможных сценариев подтверждения платежа
     * @return string[] Список возможных сценариев подтверждения платежа
     * @see ConfirmationType
     */
    public function getConfirmationTypes()
    {
        return $this->_confirmationTypes;
    }

    /**
     * Возвращает сумму платежа
     * @return AmountInterface Сумма платежа
     */
    public function getCharge()
    {
        return $this->_charge;
    }

    /**
     * Возвращает сумму дополнительной комиссии при проведении платежа с помощью текущего способа оплаты
     * @return AmountInterface Сумма комиссии
     */
    public function getFee()
    {
        return $this->_fee;
    }

    /**
     * Возвращает признак присутствия дополнительной комиссии на стороне партнера
     * @return bool True если комиссия на стороне партнёра имеется, false если нет
     */
    public function getExtraFee()
    {
        return $this->_extraFee;
    }
}
