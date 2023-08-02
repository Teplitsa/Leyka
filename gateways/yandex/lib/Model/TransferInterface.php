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
 * Interface TransferInterface
 *
 * Данные о распределении денег — сколько и в какой магазин нужно перевести.
 * Присутствует, если вы используете решение ЮKassa для платформ.
 *
 * @property AmountInterface $amount Сумма, которую необходимо перечислить магазину
 * @property AmountInterface $platform_fee_amount Комиссия за проданные товары и услуги, которая удерживается с магазина в вашу пользу
 * @property string $accountId Идентификатор магазина, в пользу которого вы принимаете оплату
 * @property string $status Статус распределения денег между магазинами. Возможные значения: `pending`, `waiting_for_capture`, `succeeded`, `canceled`
 * @property Metadata $metadata Любые дополнительные данные, которые нужны вам для работы с платежами (например, номер заказа)
 *
 * @package YooKassa
 */
interface TransferInterface
{
    /**
     * Устанавливает идентификатор магазина-получателя средств
     *
     * @param string $value Идентификатор магазина-получателя средств
     *
     * @return void
     */
    public function setAccountId($value);

    /**
     * Возвращает идентификатор магазина-получателя средств
     *
     * @return string|null Идентификатор магазина-получателя средств
     */
    public function getAccountId();

    /**
     * Возвращает сумму оплаты
     *
     * @return AmountInterface Сумма оплаты
     */
    public function getAmount();

    /**
     * Проверяет, была ли установлена сумма оплаты
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
     * Проверяет, была ли установлена комиссия за проданные товары и услуги, которая удерживается с магазина в вашу пользу
     *
     * @return bool True если комиссия была установлена, false если нет
     */
    public function hasPlatformFeeAmount();

    /**
     * Устанавливает комиссию за проданные товары и услуги, которая удерживается с магазина в вашу пользу
     * @param AmountInterface|array $value Сумма комиссии
     */
    public function setPlatformFeeAmount($value);

    /**
     * Возвращает статус операции распределения средств конечному получателю
     * @return string|null Статус операции распределения средств конечному получателю
     */
    public function getStatus();

    /**
     * Устанавливает статус операции распределения средств конечному получателю
     * @param string|null $value
     */
    public function setStatus($value);

    /**
     * Устанавливает метаданные
     * @param Metadata|array $value Метаданные
     */
    public function setMetadata($value);

    /**
     * Возвращает метаданные
     * @return Metadata|null Метаданные
     */
    public function getMetadata();

    /**
     * Проверяет, были ли установлены метаданные
     *
     * @return bool True если метаданные были установлены, false если нет
     */
    public function hasMetadata();
}
