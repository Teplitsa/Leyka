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

namespace YooKassa\Model;


use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Class Airline
 */
class Airline extends AbstractObject implements AirlineInterface
{
    /**
     * @var string Номер бронирования. Обязателен на этапе создания платежа.
     */
    private $_bookingReference;

    /**
     * @var string Уникальный номер билета. Обязателен на этапе подтверждения платежа
     */
    private $_ticketNumber;

    /**
     * @var PassengerInterface[]
     */
    private $_passengers;

    /**
     * @var LegInterface[]
     */
    private $_legs;

    /**
     * @inheritdoc
     */
    public function getBookingReference()
    {
        return $this->_bookingReference;
    }

    /**
     * @param string $value
     */
    public function setBookingReference($value)
    {
        if ($value === null || $value === '') {
            $this->_bookingReference = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid booking reference value type', 0,
                'airline.booking_reference');
        } elseif (mb_strlen((string)$value, 'utf-8') > 20) {
            throw new InvalidPropertyValueException('Invalid booking reference value: "'.$value.'"', 0,
                'airline.booking_reference');
        } else {
            $this->_bookingReference = (string)$value;
        }
    }

    /**
     * @inheritdoc
     */
    public function getTicketNumber()
    {
        return $this->_ticketNumber;
    }

    /**
     * @param string $value
     */
    public function setTicketNumber($value)
    {
        if ($value === null || $value === '') {
            $this->_ticketNumber = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid ticket number value type', 0,
                'airline.ticket_number');
        } elseif (!preg_match('/^[0-9]{1,150}$/', (string)$value)) {
            throw new InvalidPropertyValueException('Invalid ticket_number value: "'.$value.'"', 0,
                'airline.ticket_number');
        } else {
            $this->_ticketNumber = (string)$value;
        }
    }

    /**
     * @inheritdoc
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    /**
     * @param array|PassengerInterface[] $value
     */
    public function setPassengers($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty passengers value in airline', 0, 'airline.passengers');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid passengers value type in airline', 0, 'airline.passengers', $value
            );
        }
        $this->_passengers = array();
        foreach ($value as $key => $val) {
            try {
                $this->addPassenger($val);
            } catch (InvalidPropertyValueTypeException $exception) {
                throw new InvalidPropertyValueTypeException(
                    'Invalid passenger value type in airline', 0, 'airline.passengers['.$key.']', $val
                );
            }
        }
    }

    /**
     * @param array|PassengerInterface $value
     */
    public function addPassenger($value)
    {
        if ($value instanceof PassengerInterface) {
            $this->_passengers[] = $value;
        } elseif (is_array($value)) {
            $passenger = new Passenger();
            $passenger->fromArray($value);
            $this->_passengers[] = $passenger;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid passenger value type in airline', 0
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getLegs()
    {
        return $this->_legs;
    }

    /**
     * @param array|LegInterface[] $value
     */
    public function setLegs($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty legs value in airline', 0, 'airline.passengers');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid legs value type in airline', 0, 'airline.legs', $value
            );
        }
        $this->_legs = array();
        foreach ($value as $key => $val) {
            try {
                $this->addLeg($val);
            } catch (InvalidPropertyValueTypeException $exception) {
                throw new InvalidPropertyValueTypeException(
                    'Invalid legs value type in airline', 0, 'airline.legs['.$key.']', $val
                );
            }
        }
    }

    /**
     * @param array|LegInterface $value
     */
    public function addLeg($value)
    {
        if ($value instanceof LegInterface) {
            $this->_legs[] = $value;
        } elseif (is_array($value)) {
            $leg = new Leg();
            $leg->fromArray($value);
            $this->_legs[] = $leg;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid passenger value type in airline', 0
            );
        }
    }

    /**
     * Првоерка на наличие данных
     * @return bool
     */
    public function notEmpty()
    {
        return $this->_legs || $this->_passengers || $this->_ticketNumber || $this->_bookingReference;
    }

    /**
     * @inheritdoc
     */
    public function fromArray($sourceArray)
    {
        if (is_array($sourceArray['passengers']) && !empty($sourceArray['passengers'])) {
            $sourceArray['passengers'] = array_map(function ($passengerData) {
                if (is_array($passengerData)) {
                    $passenger = new Passenger();
                    $passenger->fromArray($passengerData);

                    return $passenger;
                } elseif ($passengerData instanceof PassengerInterface) {
                    return $passengerData;
                }
            }, $sourceArray['passengers']);
        }

        if (is_array($sourceArray['legs']) && !empty($sourceArray['legs'])) {
            $sourceArray['legs'] = array_map(function ($legData) {
                if (is_array($legData)) {
                    $leg = new Leg();
                    $leg->fromArray($legData);

                    return $leg;
                } elseif ($legData instanceof LegInterface) {
                    return $legData;
                }
            }, $sourceArray['legs']);
        }
        parent::fromArray($sourceArray);
    }
}