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

namespace YooKassa\Model;

/**
 * Interface ReceiptCustomerInterface
 *
 * @package YooKassa
 *
 * @property-read string $fullName Для юрлица — название организации, для ИП и физического лица — ФИО.
 * @property-read string $full_name Для юрлица — название организации, для ИП и физического лица — ФИО.
 * @property-read string $phone Номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек.
 * @property-read string $email E-mail адрес плательщика на который будет выслан чек.
 * @property-read string $inn ИНН плательщика (10 или 12 цифр).
 */
interface ReceiptCustomerInterface
{
    /**
     * Возвращает название организации или ФИО физического лица
     *
     * @return string Название организации или ФИО физического лица
     */
    function getFullName();

    /**
     * Возвращает номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек
     *
     * @return string Номер телефона плательщика
     */
    function getPhone();

    /**
     * Возвращает адрес электронной почты на который будет выслан чек
     *
     * @return string E-mail адрес плательщика
     */
    function getEmail();

    /**
     * Возвращает ИНН плательщика
     *
     * @return string ИНН плательщика
     */
    function getInn();

    /**
     * Возвращает массив полей плательщика
     *
     * @return array
     */
    function jsonSerialize();
}
