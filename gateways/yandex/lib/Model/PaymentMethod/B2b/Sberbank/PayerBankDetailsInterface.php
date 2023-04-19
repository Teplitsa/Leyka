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

namespace YooKassa\Model\PaymentMethod\B2b\Sberbank;

/**
 * Interface PayerBankDetailsInterface
 *
 * @package YooKassa
 *
 * @property-read string $fullName Полное наименование организации
 * @property-read string $shortName Сокращенное наименование организации
 * @property-read string $address Адрес организации
 * @property-read string $inn ИНН организации
 * @property-read string $kpp КПП организации
 * @property-read string $bankName Наименование банка организации
 * @property-read string $bankBranch Отделение банка организации
 * @property-read string $bankBik БИК банка организации
 * @property-read string $account Номер счета организации
 */
interface PayerBankDetailsInterface
{
    /**
     * Возвращает полное наименование организации
     * @return string Полное наименование организации
     */
    function getFullName();

    /**
     * Возвращает сокращенное наименование организации
     * @return string Сокращенное наименование организации
     */
    function getShortName();

    /**
     * Возвращает адрес организации
     * @return string Адрес организации
     */
    function getAddress();

    /**
     * Возвращает ИНН организации
     * @return string ИНН организации
     */
    function getInn();

    /**
     * Возвращает КПП организации
     * @return string КПП организации
     */
    function getKpp();

    /**
     * Возвращает наименование банка организации
     * @return string Наименование банка организации
     */
    function getBankName();

    /**
     * Возвращает отделение банка организации
     * @return string Отделение банка организации
     */
    function getBankBranch();

    /**
     * Возвращает БИК банка организации
     * @return string БИК банка организации
     */
    function getBankBik();

    /**
     * Возвращает номер счета организации
     * @return string Номер счета организации
     */
    function getAccount();

}

