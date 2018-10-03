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

/**
 * Класс сериализатора объектов запросов к API для получения списка возвратов
 *
 * @package YandexCheckout\Request\Refunds
 */
class RefundsRequestSerializer
{
    /**
     * @var array Карта маппинга свойств объекта запроса на поля отправляемого запроса
     */
    private static $propertyMap = array(
        'refundId'       => 'refund_id',
        'paymentId'      => 'payment_id',
        'gatewayId'      => 'gateway_id',
        'createdGte'     => 'created_gte',
        'createdGt'      => 'created_gt',
        'createdLte'     => 'created_lte',
        'createdLt'      => 'created_lt',
        'authorizedGte'  => 'authorized_gte',
        'authorizedGt'   => 'authorized_gt',
        'authorizedLte'  => 'authorized_lte',
        'authorizedLt'   => 'authorized_lt',
        'status'         => 'status',
        'nextPage'       => 'next_page',
    );

    /**
     * Сериализует объект запроса к API для дальнейшей его отправки
     * @param RefundsRequestInterface $request Сериализуемый объект
     * @return array Массив с инфомрацией, отпарвляемый в дальнейшем в API
     */
    public function serialize(RefundsRequestInterface $request)
    {
        $result = array(
            'account_id' => $request->getAccountId(),
        );
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