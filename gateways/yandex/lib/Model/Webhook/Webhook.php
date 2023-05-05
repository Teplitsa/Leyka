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

namespace YooKassa\Model\Webhook;


use YooKassa\Common\AbstractObject;
use YooKassa\Model\NotificationEventType;

/**
 * Класс Webhook содержит информацию о подписке на одно событие
 *
 * @property string $id Идентификатор webhook
 * @property string $event Событие, о котором уведомляет ЮKassa
 * @property string $url URL, на который ЮKassa будет отправлять уведомления
 *
 * @package YooKassa
 */
class Webhook extends AbstractObject
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * @see NotificationEventType
     */
    private $event;

    /**
     * @var string
     */
    private $url;

    /**
     * Возвращает идентификатор webhook
     * @return string Идентификатор webhook
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Устанавливает идентификатор webhook
     * @param mixed $id Идентификатор webhook
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Возвращает событие, о котором уведомляет ЮKassa
     * @return string Событие, о котором уведомляет ЮKassa
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Устанавливает событие, о котором уведомляет ЮKassa
     * @param string $event Событие, о котором уведомляет ЮKassa
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Возвращает URL, на который ЮKassa будет отправлять уведомления
     * @return string URL, на который ЮKassa будет отправлять уведомления
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Устанавливает URL, на который ЮKassa будет отправлять уведомления
     * @param string $url URL, на который ЮKassa будет отправлять уведомления
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
