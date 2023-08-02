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

use DateTime;

/**
 * Interface DealInterface
 *
 * @package YooKassa
 *
 * @property string $id Идентификатор сделки
 * @property string $status Статус сделки
 * @property string $type Тип сделки
 * @property string $fee_moment Момент перечисления вознаграждения
 * @property string $feeMoment Момент перечисления вознаграждения
 * @property string $description Описание сделки
 * @property MonetaryAmount $balance Баланс сделки
 * @property MonetaryAmount $payout_balance Сумма вознаграждения продавца
 * @property MonetaryAmount $payoutBalance Сумма вознаграждения продавца
 * @property DateTime $created_at Время создания сделки
 * @property DateTime $createdAt Время создания сделки
 * @property DateTime $expires_at Время автоматического закрытия сделки
 * @property DateTime $expiresAt Время автоматического закрытия сделки
 * @property Metadata $metadata Дополнительные данные сделки
 * @property bool $test Признак тестовой операции
 */
interface DealInterface
{
    /**
     * Возвращает Id сделки
     *
     * @return string Id сделки
     */
    public function getId();

    /**
     * Возвращает тип сделки
     *
     * @return string Тип сделки
     */
    public function getType();

    /**
     * Возвращает момент перечисления вам вознаграждения платформы
     *
     * @return string Момент перечисления вознаграждения
     */
    public function getFeeMoment();

    /**
     * Возвращает описание сделки (не более 128 символов).
     *
     * @return string Описание сделки
     */
    public function getDescription();

    /**
     * Возвращает баланс сделки
     *
     * @return MonetaryAmount Баланс сделки
     */
    public function getBalance();

    /**
     * Возвращает сумму вознаграждения продавца
     *
     * @return MonetaryAmount Сумма вознаграждения продавца
     */
    public function getPayoutBalance();

    /**
     * Возвращает статус сделки
     *
     * @return string Статус сделки
     */
    public function getStatus();

    /**
     * Возвращает время создания сделки
     *
     * @return DateTime Время создания сделки
     */
    public function getCreatedAt();

    /**
     * Возвращает время автоматического закрытия сделки
     *
     * @return DateTime Время автоматического закрытия сделки
     */
    public function getExpiresAt();

    /**
     * Возвращает дополнительные данные сделки
     *
     * @return Metadata Дополнительные данные сделки
     */
    public function getMetadata();

    /**
     * Возвращает признак тестовой операции
     *
     * @return bool Признак тестовой операции
     */
    public function getTest();
}
