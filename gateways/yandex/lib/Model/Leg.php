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

class Leg extends AbstractObject implements LegInterface
{
    const ISO8601 = 'Y-m-d';
    /**
     * @var string Трёхбуквенный IATA-код аэропорта вылета
     */
    private $_departureAirport;

    /**
     * @var string Трёхбуквенный IATA-код аэропорта прилёта
     */
    private $_destinationAirport;

    /**
     * @var string Дата вылета в формате YYYY-MM-DD ISO 8601:2004
     */
    private $_departureDate;

    /**
     * @inheritdoc
     */
    public function getDepartureAirport()
    {
        return $this->_departureAirport;
    }

    /**
     * @param string $value
     */
    public function setDepartureAirport($value)
    {
        if (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid departure_airport value type', 0,
                'airline.departure_airport');
        } elseif (!preg_match('/^[A-Z]{3}$/', (string)$value)) {
            throw new InvalidPropertyValueException('Invalid departure_airport value: "'.$value.'"', 0,
                'airline.departure_airport');
        } else {
            $this->_departureAirport = (string)$value;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDestinationAirport()
    {
        return $this->_destinationAirport;
    }

    /**
     * @param string $value
     */
    public function setDestinationAirport($value)
    {
        if (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid destination_airport value type', 0,
                'airline.destination_airport');
        } elseif (!preg_match('/^[A-Z]{3}$/', (string)$value)) {
            throw new InvalidPropertyValueException('Invalid destination_airport value: "'.$value.'"', 0,
                'airline.destination_airport');
        } else {
            $this->_destinationAirport = (string)$value;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDepartureDate()
    {
        return $this->_departureDate;
    }

    /**
     * @param \DateTime|string $value
     */
    public function setDepartureDate($value)
    {
        if (TypeCast::canCastToDateTime($value)) {
            $departureDate = TypeCast::castToDateTime($value);
            if ($departureDate === null) {
                throw new InvalidPropertyValueException(
                    'Invalid departure_date value in airline.legs', 0, 'airline.legs'
                );
            }
            $this->_departureDate = $departureDate->format(self::ISO8601);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid departure_date value type in airline.legs', 0, 'airline.legs'
            );
        }
    }
}