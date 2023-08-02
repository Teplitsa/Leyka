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

namespace YooKassa\Request\Receipts;


use YooKassa\Model\ReceiptType;

/**
 * Фабричный класс для работы с чеками
 *
 * @package YooKassa
 */
class ReceiptResponseFactory
{
    private $typeClassMap = array(
        ReceiptType::PAYMENT => 'PaymentReceiptResponse',
        ReceiptType::REFUND  => 'RefundReceiptResponse',
        ReceiptType::SIMPLE  => 'SimpleReceiptResponse',
    );

    /**
     * Фабричный метод для работы с чеками
     *
     * @param array $data Массив с данными чека
     *
     * @return AbstractReceiptResponse|SimpleReceiptResponse|PaymentReceiptResponse|RefundReceiptResponse Объект чека определенного типа
     */
    public function factory($data)
    {
        if (array_key_exists('type', $data)) {
            if (!is_string($data['type'])) {
                throw new \InvalidArgumentException('Invalid receipt type value in receipt factory');
            }
            if (!in_array($data['type'], ReceiptType::getEnabledValues())) {
                throw new \InvalidArgumentException('Invalid receipt data type "' . $data['type'] . '"');
            }
            if (array_key_exists('refund_id', $data)) {
                $type = ReceiptType::REFUND;
            } elseif (array_key_exists('payment_id', $data)) {
                $type = ReceiptType::PAYMENT;
            } else {
                $type = ReceiptType::SIMPLE;
            }
        } else {
            throw new \InvalidArgumentException(
                'Parameter type not specified in ReceiptResponseFactory.factory()'
            );
        }

        $className = __NAMESPACE__ . '\\' . $this->typeClassMap[$type];

        return new $className($data);
    }
}
