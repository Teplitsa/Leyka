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

namespace YooKassa\Request\Deals;

use Exception;
use YooKassa\Common\AbstractObject;
use YooKassa\Model\DealInterface;

/**
 * Класс объекта ответа от API со списком сделок магазина
 *
 * @package YooKassa
 */
class DealsResponse extends AbstractObject
{
    /**
     * @var DealInterface[] Массив сделок
     */
    private $items;

    /**
     * @var string|null Токен следующей страницы
     */
    private $nextCursor;

    /**
     * Конструктор, устанавливает свойства объекта из пришедшего из API ассоциативного массива
     *
     * @param array $sourceArray Массив настроек, пришедший от API
     * @throws Exception
     */
    public function fromArray($sourceArray)
    {
        $this->items = array();
        foreach ($sourceArray['items'] as $dealInfo) {
            $this->items[] = new DealResponse($dealInfo);
        }
        if (!empty($sourceArray['next_cursor'])) {
            $this->nextCursor = $sourceArray['next_cursor'];
        }
    }

    /**
     * Возвращает список сделок
     * @return DealInterface[] Список сделок
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Возвращает токен следующей страницы, если он задан, или null
     * @return string|null Токен следующей страницы
     */
    public function getNextCursor()
    {
        return $this->nextCursor;
    }

    /**
     * Проверяет, имеется ли в ответе токен следующей страницы
     * @return bool True если токен следующей страницы есть, false если нет
     */
    public function hasNextCursor()
    {
        return $this->nextCursor !== null;
    }

}
