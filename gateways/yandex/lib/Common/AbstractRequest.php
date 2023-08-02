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

namespace YooKassa\Common;

/**
 * Базовый класс объекта запроса, передаваемого в методы клиента API
 *
 * @package YooKassa
 */
abstract class AbstractRequest extends AbstractObject
{
    /**
     * @var string Последняя ошибка валидации текущего запроса
     */
    private $_validationError;

    /**
     * Валидирует текущий запрос, проверяет все ли нужные свойства установлены
     * @return bool True если запрос валиден, false если нет
     */
    abstract public function validate();

    /**
     * Очищает статус валидации текущего запроса
     */
    public function clearValidationError()
    {
        $this->_validationError = null;
    }

    /**
     * Устанавливает ошибку валидации
     * @param string $value Ошибка, произошедшая при валидации объекта
     */
    protected function setValidationError($value)
    {
        $this->_validationError = $value;
    }

    /**
     * Возвращает последнюю ошибку валидации
     * @return string Последняя произошедшая ошибка валидации
     */
    public function getLastValidationError()
    {
        return $this->_validationError;
    }
}
