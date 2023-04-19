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

namespace YooKassa\Model\Confirmation;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\ConfirmationType;

/**
 * Способ подтверждения платежа.
 *
 * @property-read string $type
 *
 * @method string getConfirmationUrl() Для ConfirmationRedirect
 * @method string getConfirmationToken() Для ConfirmationEmbedded
 * @method string getConfirmationData() Для ConfirmationQr
 */
abstract class AbstractConfirmation extends AbstractObject
{
    /**
     * Тип подтверждения платежа
     * @var string
     */
    private $_type;

    /**
     * Возвращает тип подтверждения платежа
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип подтверждения платежа
     * @param string $value
     */
    protected function setType($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "type" parameter in Confirmation', 0, 'confirmation.type'
            );
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (ConfirmationType::valueExists($value)) {
                $this->_type = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid value for "type" parameter in Confirmation', 0, 'confirmation.type', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "type" parameter in Confirmation', 0, 'confirmation.type', $value
            );
        }
    }
}
