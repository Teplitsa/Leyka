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

namespace YooKassa\Request\Webhook;

use YooKassa\Model\Webhook\Webhook;

/**
 * Актуальный список объектов webhook для переданного OAuth-токена
 *
 * @package YooKassa
 */
class WebhookListResponse
{
    /**
     * Тип ответа
     * @var string
     */
    private $type;

    /**
     * Список установленных webhook для переданного OAuth-токена
     * @var Webhook[] Список установленных webhook
     */
    private $items;

    /**
     * Конструктор, устанавливает список полученных от API установленных webhook для переданного OAuth-токена
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
     * Возвращает тип ответа. Доступен только `list`
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Возвращает список установленных webhook для переданного OAuth-токена
     * @return Webhook[] Список установленных webhook
     */
    public function getItems()
    {
        return $this->items;
    }
}
