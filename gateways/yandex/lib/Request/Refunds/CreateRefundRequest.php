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

namespace YooKassa\Request\Refunds;

use YooKassa\Common\AbstractRefundRequest;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Deal\RefundDealData;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\Source;
use YooKassa\Model\SourceInterface;

/**
 * Класс объекта запроса для создания возврата
 *
 * @example 02-builder.php 148 35 Пример использования билдера
 *
 * @property string $paymentId Айди платежа для которого создаётся возврат
 * @property AmountInterface $amount Сумма возврата
 * @property string $description Комментарий к операции возврата, основание для возврата средств покупателю.
 * @property ReceiptInterface|null $receipt Инстанс чека или null
 * @property SourceInterface[]|null $sources Информация о распределении денег — сколько и в какой магазин нужно перевести
 * @property RefundDealData|null $deal Информация о сделке
 */
class CreateRefundRequest extends AbstractRefundRequest implements CreateRefundRequestInterface
{

    /**
     * @var string Комментарий к операции возврата, основание для возврата средств покупателю.
     */
    private $_description;

    /**
     * @var SourceInterface[]
     */
    private $_sources;

    /**
     * @var RefundDealData
     */
    private $_deal;

    /**
     * Возвращает комментарий к возврату или null, если комментарий не задан
     * @return string Комментарий к операции возврата, основание для возврата средств покупателю.
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Проверяет задан ли комментарий к создаваемому возврату
     * @return bool True если комментарий установлен, false если нет
     */
    public function hasDescription()
    {
        return $this->_description !== null;
    }

    /**
     * Устанавливает комментарий к возврату
     * @param string $value Комментарий к операции возврата, основание для возврата средств покупателю
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если была передана не строка
     */
    public function setDescription($value)
    {
        if ($value === null || $value === '') {
            $this->_description = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid description value type in CreateRefundRequest', 0, 'CreateRefundRequest.description', $value
            );
        }
    }

    /**
     * Удаляет чек из запроса
     */
    public function removeReceipt()
    {
        $this->setReceipt(null);
    }

    /**
     * Устанавливает sources (массив распределения денег между магазинами)
     * @param SourceInterface[]|array $value Массив распределения денег между магазинами
     */
    public function setSources($value)
    {
        if (!is_array($value)) {
            $message = 'Sources must be an array of SourceInterface';
            throw new InvalidPropertyValueTypeException($message, 0, 'CreateRefundRequest.sources', $value);
        }

        $sources = array();
        foreach ($value as $item) {
            if (is_array($item)) {
                $item = new Source($item);
            }

            if (!($item instanceof SourceInterface)) {
                $message = 'Source must be instance of SourceInterface';
                throw new InvalidPropertyValueTypeException($message, 0, 'CreateRefundRequest.sources', $value);
            }
            $sources[] = $item;
        }

        $this->_sources = $sources;
    }

    /**
     * Возвращает информацию о распределении денег — сколько и в какой магазин нужно перевести
     * @return SourceInterface[] Информация о распределении денег
     */
    public function getSources()
    {
        return $this->_sources;
    }

    /**
     * Проверяет наличие информации о распределении денег
     * @return bool
     */
    public function hasSources()
    {
        return !empty($this->_sources);
    }

    /**
     * Возвращает билдер объектов текущего типа
     * @return CreateRefundRequestBuilder Инстанс билдера запросов
     */
    public static function builder()
    {
        return new CreateRefundRequestBuilder();
    }

    /**
     * Возвращает данные о сделке, в составе которой проходит возврат
     * @return RefundDealData Данные о сделке, в составе которой проходит возврат
     */
    public function getDeal()
    {
        return $this->_deal;
    }

    /**
     * Проверяет, были ли установлены данные о сделке
     * @return bool True если данные о сделке были установлены, false если нет
     */
    public function hasDeal()
    {
        return !empty($this->_deal);
    }

    /**
     * Устанавливает данные о сделке, в составе которой проходит возврат
     * @param RefundDealData|array|null $value Данные о сделке, в составе которой проходит возврат
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданные данные не удалось интерпретировать как данные сделки
     */
    public function setDeal($value)
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->_deal = null;
        } elseif ($value instanceof RefundDealData) {
            $this->_deal = $value;
        } elseif (is_array($value)) {
            $this->_deal = new RefundDealData($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid deal value type in CreateRefundRequest', 0, 'CreateRefundRequest.deal', $value
            );
        }
    }
}
