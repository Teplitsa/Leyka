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

namespace YandexCheckout\Request\Refunds;

use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\Refund;

/**
 * Абстрактный класс ответа от API с информацией о возврате
 *
 * @package YandexCheckout\Request\Refunds
 */
abstract class AbstractRefundResponse extends Refund
{
    /**
     * Конструктор
     * @param array $options Ассоциативный массив с информацией, вернувшейся от API
     */
    public function __construct(array $options)
    {
        $this->setId(empty($options['id']) ? null : $options['id']);
        $this->setPaymentId(empty($options['payment_id']) ? null : $options['payment_id']);
        $this->setStatus(empty($options['status']) ? null : $options['status']);
        $this->setCreatedAt(empty($options['created_at']) ? null : $options['created_at']);
        $this->setAmount(new MonetaryAmount($options['amount']['value'], $options['amount']['currency']));

        if (!empty($options['receipt_registration'])) {
            $this->setReceiptRegistration($options['receipt_registration']);
        }

        if (!empty($options['comment'])) {
            $this->setComment($options['comment']);
        }
    }
}
