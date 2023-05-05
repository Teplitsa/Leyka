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
 * Interface SupplierInterface
 *
 * Информация о поставщике товара или услуги.
 *
 * Можно передавать, если вы отправляете данные для формирования чека по сценарию - сначала платеж, потом чек.

 * @property string $name Наименование поставщика
 * @property string $phone Телефон пользователя. Указывается в формате ITU-T E.164
 * @property string $inn ИНН пользователя (10 или 12 цифр)
 *
 * @package YooKassa
 */
interface SupplierInterface
{
    /**
     * Возвращает наименование поставщика
     * @return string|null
     */
    public function getName();

    /**
     * Устанавливает наименование поставщика
     * @param string|null $value Наименование поставщика
     */
    public function setName($value);

    /**
     * Возвращает Телефон пользователя. Указывается в формате ITU-T E.164
     * @return string|null Телефон пользователя
     */
    public function getPhone();

    /**
     * Устанавливает Телефон пользователя. Указывается в формате ITU-T E.164
     * @param string|null $value Телефон пользователя
     */
    public function setPhone($value);

    /**
     * Возвращает ИНН пользователя (10 или 12 цифр)
     * @return string|null ИНН пользователя
     */
    public function getInn();

    /**
     * Устанавливает ИНН пользователя (10 или 12 цифр)
     * @param $value ИНН пользователя
     */
    public function setInn($value);
}
