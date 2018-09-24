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

namespace YandexCheckout\Model;


use YandexCheckout\Common\AbstractObject;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;

class Passenger extends AbstractObject implements PassengerInterface
{
    /**
     * @var string
     */
    private $_firstName;

    /**
     * @var string
     */
    private $_lastName;

    /**
     * @inheritdoc
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * @param $value
     */
    public function setFirstName($value)
    {
        if (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > 64) {
                throw new InvalidPropertyValueException(
                    'Invalid first_name value length in Passenger object',
                    0, 'airline.passengers', $value
                );
            }
            $this->_firstName = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid first_name value type in Passenger object', 0, 'airline.passengers', $value
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * @param $value
     */
    public function setLastName($value)
    {
        if (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > 64) {
                throw new InvalidPropertyValueException(
                    'Invalid last_name value length in Passenger object',
                    0, 'airline.passengers', $value
                );
            }
            $this->_lastName = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid last_name value type in Passenger object', 0, 'airline.passengers', $value
            );
        }
    }
}