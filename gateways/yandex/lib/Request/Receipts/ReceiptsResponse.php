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
 * Class ReceiptsResponse
 * @package YooKassa\Request\Receipts
 */
class ReceiptsResponse
{
    /**
     * Формат выдачи результатов запроса. Возможное значение: `list` (список).
     *
     * @var string Формат выдачи результатов запроса.
     */
    private $type;

    /**
     * Список чеков
     *
     * @var ReceiptResponseInterface[] Список чеков
     */
    private $items;

    /**
     * @var string|null Токен следующей страницы
     */
    private $nextCursor;

    /**
     * @var ReceiptResponseFactory Фабрика для создания чеков
     */
    private $factory;

    /**
     * Конструктор, устанавливает список полученых от API чеков
     *
     * @param array $response Разобранный ответ от API в виде чеков
     * @throws \Exception
     */
    public function __construct($response)
    {
        $this->factory = new ReceiptResponseFactory();

        if (!empty($response['type'])) {
            $this->type = $response['type'];
        }

        $this->items = array();
        foreach ($response['items'] as $item) {
            if ($receipt = $this->factory->factory($item)) {
                $this->items[] = $receipt;
            }
        }

        if (!empty($response['next_cursor'])) {
            $this->nextCursor = $response['next_cursor'];
        }
    }

    /**
     * Возвращает формат выдачи результатов запроса. Возможное значение: `list` (список).
     * @return string Формат выдачи результатов запроса.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Возаращает список чеков
     * @return ReceiptResponseInterface[] Список чеков
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
     * Проверяет имееотся ли в ответе токен следующей страницы
     * @return bool True если токен следующей страницы есть, false если нет
     */
    public function hasNextCursor()
    {
        return $this->nextCursor !== null;
    }
}