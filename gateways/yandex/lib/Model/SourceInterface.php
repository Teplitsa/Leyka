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

/**
 * Interface TransferInterface
 *
 * @package YooKassa\Model
 *
 * @property AmountInterface $amount
 * @property AmountInterface $platform_fee_amount
 * @property string $accountId
 */
interface SourceInterface
{
    /**
     * Устаналивает id магазина-получателя средств
     *
     * @param string $value
     *
     * @return void
     */
    public function setAccountId($value);

    /**
     * Возвращает id магазина с которого будут списаны средства
     *
     * @return string|null
     */
    public function getAccountId();

    /**
     * Возвращает сумму оплаты
     *
     * @return AmountInterface Сумма оплаты
     */
    public function getAmount();

    /**
     * Проверяет была ли установлена сумма оплаты
     *
     * @return bool True если сумма оплаты была установлена, false если нет
     */
    public function hasAmount();

    /**
     * Устанавливает сумму оплаты
     * @param AmountInterface|array $value Сумма оплаты
     */
    public function setAmount($value);

    /**
     * Возвращает комиссию за проданные товары и услуги, которая удерживается с магазина в вашу пользу
     *
     * @return AmountInterface Сумма комиссии
     */
    public function getPlatformFeeAmount();

    /**
     * Проверяет была ли установлена комиссия за проданные товары и услуги, которая удерживается с магазина в вашу пользу
     *
     * @return bool True если комиссия была установлена, false если нет
     */
    public function hasPlatformFeeAmount();

    /**
     * Устанавливает комиссию за проданные товары и услуги, которая удерживается с магазина в вашу пользу
     * @param AmountInterface|array $value Сумма комиссии
     */
    public function setPlatformFeeAmount($value);
}
