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

use YandexCheckout\Model\RefundInterface;

/**
 * Класс объекта ответа от API со списком возвратов магазина
 *
 * @package YandexCheckout\Request\Refunds
 */
class RefundsResponse
{
    /**
     * @var RefundInterface[] Массив возвратов
     */
    private $items;

    /**
     * @var string|null Токен следующей страницы
     */
    private $nextPage;

    /**
     * Конструктор, устанавливает свойства объекта из пришедшего из API ассоциативного массива
     * @param array $options Массив настроек, пришедший от API
     */
    public function __construct(array $options)
    {
        $this->items = array();
        foreach ($options['items'] as $item) {
            $this->items[] = new RefundResponse($item);
        }
        if (!empty($options['next_page'])) {
            $this->nextPage = $options['next_page'];
        }
    }

    /**
     * Возвращает список возвратов
     * @return RefundInterface[] Список возвратов
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Возвращает токен следующей страницы, если он задан, или null
     * @return string|null Токен следующей страницы
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * Проверяет имееотся ли в ответе токен следующей страницы
     * @return bool True если токен следующей страницы есть, false если нет
     */
    public function hasNextPage()
    {
        return $this->nextPage !== null;
    }
}
