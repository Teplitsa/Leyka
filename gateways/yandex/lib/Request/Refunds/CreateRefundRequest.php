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

use YandexCheckout\Common\AbstractPaymentRequest;
use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;
use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\ReceiptInterface;

/**
 * Класс объекта запроса для создания возврата
 *
 * @property string $paymentId Айди платежа для которого создаётся возврат
 * @property AmountInterface $amount Сумма возврата
 * @property string $comment Комментарий к операции возврата, основание для возврата средств покупателю.
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
    private $_comment;

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
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * Проверяет задан ли комментарий к создаваемому возврату
     * @return bool True если комментарий установлен, false если нет
     */
    public function hasComment()
    {
        return $this->_comment !== null;
    }

    /**
     * Устанавливает комментарий к возврату
     * @param string $value Комментарий к операции возврата, основание для возврата средств покупателю
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка длинее 250 символов
     * @throws InvalidPropertyValueTypeException Выбрасывается если была передана не строка
     */
    public function setComment($value)
    {
        if ($value === null || $value === '') {
            $this->_comment = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen($value, 'utf-8');
            if ($length > 250) {
                throw new InvalidPropertyValueException(
                    'Invalid commend value in CreateRefundRequest', 0, 'CreateRefundRequest.comment', $value
                );
            }
            $this->_comment = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid commend value type in CreateRefundRequest', 0, 'CreateRefundRequest.comment', $value
            );
        }
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
