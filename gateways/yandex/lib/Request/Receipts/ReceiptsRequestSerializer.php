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

namespace YooKassa\Request\Receipts;

/**
 * Класс сериализатора объектов запросов к API для получения списка возвратов
 *
 * @package YooKassa\Request\Receipts
 */
class ReceiptsRequestSerializer
{
    /**
     * @var array Карта маппинга свойств объекта запроса на поля отправляемого запроса
     */
    private static $propertyMap = array(
        'cursor'         => 'cursor',
        'createdAtGte'   => 'created_at.gte',
        'createdAtGt'    => 'created_at.gt',
        'createdAtLte'   => 'created_at.lte',
        'createdAtLt'    => 'created_at.lt',
        'limit'          => 'limit',
        'paymentId'      => 'payment_id',
        'refundId'       => 'refund_id',
        'status'         => 'status',
    );

    /**
     * Сериализует объект запроса к API для дальнейшей его отправки
     * @param ReceiptsRequestInterface $request Сериализуемый объект
     * @return array Массив с инфомрацией, отпарвляемый в дальнейшем в API
     */
    public function serialize(ReceiptsRequestInterface $request)
    {
        $result = array();
        foreach (self::$propertyMap as $property => $name) {
            $value = $request->{$property};
            if (!empty($value)) {
                if ($value instanceof \DateTime) {
                    if ($value->getTimestamp() > 1) {
                        $result[$name] = $value->format(DATE_ATOM);
                    }
                } else {
                    $result[$name] = $value;
                }
            }
        }
        return $result;
    }
}