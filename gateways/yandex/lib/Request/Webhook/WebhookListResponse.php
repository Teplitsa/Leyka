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

namespace YooKassa\Request\Webhook;

use YooKassa\Model\Webhook\Webhook;

class WebhookListResponse
{
    private $type;

    /**
     * Список способов оплаты подходящих для оплаты заказа
     * Если нет ни одного доступного способа оплаты, список будет пустым
     * @var Webhook[] Список способов оплаты
     */
    private $items;

    /**
     * Конструктор, устанавливает список полученныз от API способов оплаты
     *
     * @param array $response Разобранный ответ от API в виде массива
     */
    public function __construct($response)
    {
        if (!empty($response['type'])) {
            $this->type = $response['type'];
        }
        $this->items = array();
        foreach ($response['items'] as $item) {
            $this->items[] = new Webhook($item);
        }
    }

    /**
     * Возаращает список способов оплаты подходящих для оплаты заказа
     * Если нет ни одного доступного способа оплаты, список будет пустым
     * @return Webhook[] Список способов оплаты
     */
    public function getItems()
    {
        return $this->items;
    }
}