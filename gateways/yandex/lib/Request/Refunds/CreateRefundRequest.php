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

namespace YooKassa\Request\Refunds;

use YooKassa\Common\AbstractPaymentRequest;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\Source;
use YooKassa\Model\SourceInterface;

/**
 * Класс объекта запроса для создания возврата
 *
 * @property string $paymentId Айди платежа для которого создаётся возврат
 * @property AmountInterface $amount Сумма возврата
 * @property string $description Комментарий к операции возврата, основание для возврата средств покупателю.
 * @property ReceiptInterface|null $receipt Инстанс чека или null
 */
class CreateRefundRequest extends AbstractPaymentRequest implements CreateRefundRequestInterface
{
    /**
     * @var string Айди платежа для которого создаётся возврат
     */
    private $_paymentId;

    /**
     * @var string Комментарий к операции возврата, основание для возврата средств покупателю.
     */
    private $_description;

    /**
     * @var SourceInterface[]
     */
    private $_sources;

    /**
     * Возвращает айди платежа для которого создаётся возврат средств
     * @return string Айди платежа для которого создаётся возврат
     */
    public function getPaymentId()
    {
        return $this->_paymentId;
    }

    /**
     * Устанавливает айди платежа для которого создаётся возврат
     * @param string $value Айди платежа
     *
     * @throws EmptyPropertyValueException Выбрасывается если передано пустое значение айди платежа
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение является строкой, но не является
     * валидным значением айди платежа
     * @throws InvalidPropertyValueTypeException Выбрасывается если передано значение не валидного типа
     */
    public function setPaymentId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty payment id value in CreateRefundRequest', 0, 'CreateRefundRequest.paymentId'
            );
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen($value, 'utf-8');
            if ($length != 36) {
                throw new InvalidPropertyValueException(
                    'Invalid payment id value in CreateRefundRequest', 0, 'CreateRefundRequest.paymentId', $value
                );
            }
            $this->_paymentId = (string)$value;
        } else {
            throw new InvalidPropertyValueException(
                'Invalid payment id value type in CreateRefundRequest', 0, 'CreateRefundRequest.paymentId', $value
            );
        }
    }

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
     * Устанавливает transfers (массив распределения денег между магазинами)
     * @param SourceInterface[]|array $value
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
     * @return SourceInterface[]
     */
    public function getSources()
    {
        return $this->_sources;
    }

    /**
     * @return bool
     */
    public function hasSources()
    {
        return !empty($this->_sources);
    }

    /**
     * Валидирует текущий объект запроса
     * @return bool True если текущий объект запроса валиден, false если нет
     */
    public function validate()
    {
        if (!parent::validate()) {
            return false;
        }

        if (empty($this->_paymentId)) {
            $this->setValidationError('Payment id not specified');
            return false;
        }
        return true;
    }

    /**
     * Возвращает билдер объектов текущего типа
     * @return CreateRefundRequestBuilder Инстанс билдера запрсов
     */
    public static function builder()
    {
        return new CreateRefundRequestBuilder();
    }
}
