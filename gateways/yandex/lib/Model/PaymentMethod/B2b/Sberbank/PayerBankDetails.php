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

namespace YooKassa\Model\PaymentMethod\B2b\Sberbank;

use YooKassa\Common\AbstractObject;

/**
 * Банковские реквизиты плательщика
 * @property string $fullName Полное наименование организации
 * @property string $shortName Сокращенное наименование организации
 * @property string $address Адрес организации
 * @property string $inn ИНН организации
 * @property string $kpp КПП организации
 * @property string $bankName Наименование банка организации
 * @property string $bankBranch Отделение банка организации
 * @property string $bankBik БИК банка организации
 * @property string $account Номер счета организации
 */
class PayerBankDetails extends AbstractObject implements PayerBankDetailsInterface
{
    /**
     * @var string Полное наименование организации
     */
    private $_fullName;

    /**
     * @var string Сокращенное наименование организации
     */
    private $_shortName;

    /**
     * @var string Адрес организации
     */
    private $_address;

    /**
     * @var string ИНН организации
     */
    private $_inn;

    /**
     * @var string КПП организации
     */
    private $_kpp;

    /**
     * @var string Наименование банка организации
     */
    private $_bankName;

    /**
     * @var string Отделение банка организации
     */
    private $_bankBranch;

    /**
     * @var string БИК банка организации
     */
    private $_bankBik;

    /**
     * @var string Номер счета организации
     */
    private $_account;

    /**
     * Возвращает полное наименование организации
     * @return string Полное наименование организации
     */
    public function getFullName()
    {
        return $this->_fullName;
    }

    /**
     * @param string $value
     */
    public function setFullName($value)
    {
        $this->_fullName = $value;
    }

    /**
     * Возвращает сокращенное наименование организации
     * @return string Сокращенное наименование организации
     */
    public function getShortName()
    {
        return $this->_shortName;
    }

    /**
     * @param string $value
     */
    public function setShortName($value)
    {
        $this->_shortName = $value;
    }

    /**
     * Возвращает адрес организации
     * @return string Адрес организации
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * @param string $value
     */
    public function setAddress($value)
    {
        $this->_address = $value;
    }

    /**
     * Возвращает ИНН организации
     * @return string ИНН организации
     */
    public function getInn()
    {
        return $this->_inn;
    }

    /**
     * @param string $value
     */
    public function setInn($value)
    {
        $this->_inn = $value;
    }

    /**
     * Возвращает КПП организации
     * @return string КПП организации
     */
    public function getKpp()
    {
        return $this->_kpp;
    }

    /**
     * @param string $value
     */
    public function setKpp($value)
    {
        $this->_kpp = $value;
    }

    /**
     * Возвращает наименование банка организации
     * @return string Наименование банка организации
     */
    public function getBankName()
    {
        return $this->_bankName;
    }

    /**
     * @param string $value
     */
    public function setBankName($value)
    {
        $this->_bankName = $value;
    }

    /**
     * Возвращает отделение банка организации
     * @return string Отделение банка организации
     */
    public function getBankBranch()
    {
        return $this->_bankBranch;
    }

    /**
     * @param string $value
     */
    public function setBankBranch($value)
    {
        $this->_bankBranch = $value;
    }

    /**
     * Возвращает БИК банка организации
     * @return string БИК банка организации
     */
    public function getBankBik()
    {
        return $this->_bankBik;
    }

    /**
     * @param string $value
     */
    public function setBankBik($value)
    {
        $this->_bankBik = $value;
    }

    /**
     * Возвращает номер счета организации
     * @return string Номер счета организации
     */
    public function getAccount()
    {
        return $this->_account;
    }

    /**
     * @param string $value
     */
    public function setAccount($value)
    {
        $this->_account = $value;
    }

}