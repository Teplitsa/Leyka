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

namespace YooKassa\Model\Notification;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\NotificationType;

/**
 * Базовый класс уведомлений
 *
 * @package YooKassa\Model\Notification
 *
 * @property-read string $type Тип уведомления в виде строки
 * @property-read string $event Тип события
 */
abstract class AbstractNotification extends AbstractObject
{
    /**
     * @var string Тип уведомления
     */
    private $_type;

    /**
     * @var string Тип произошедшего события
     */
    private $_event;

    /**
     * Возвращает тип уведомления
     *
     * Тип уведомления - одна из констант, указанных в перечислении {@link NotificationType}.
     *
     * @return string Тип уведомления в виде строки
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип уведомления
     *
     * @param string $value Тип уведомления
     *
     * @throws EmptyPropertyValueException Выбрасывается если в качестве значения было передано пустое значение
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не найдено в перечислении типов
     * нотификаций
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    protected function _setType($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty parameter "type" in Notification', 0, 'notification.type');
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (NotificationType::valueExists($value)) {
                $this->_type = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid value for "type" parameter in Notification', 0, 'notification.type', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "type" parameter in Notification', 0, 'notification.type', $value
            );
        }
    }

    /**
     * Возвращает тип события
     *
     * Тип события - одна из констант, указанных в перечислении {@link NotificationEventType}.
     *
     * @return string Тип события
     */
    public function getEvent()
    {
        return $this->_event;
    }

    /**
     * Устанавливает тип события
     *
     * @param string $value Тип события
     *
     * @throws EmptyPropertyValueException Выбрасывается если в качестве значения было передано пустое значение
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не найдено в перечислении типов
     * событий
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    protected function _setEvent($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty parameter "event" in Notification', 0, 'notification.event');
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (NotificationEventType::valueExists($value)) {
                $this->_event = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid value for "event" parameter in Notification', 0, 'notification.event', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "event" parameter in Notification', 0, 'notification.event', $value
            );
        }
    }
}