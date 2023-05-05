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

namespace YooKassa\Request\Receipts;

use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptCustomerInterface;
use YooKassa\Model\ReceiptItemInterface;
use YooKassa\Model\SettlementInterface;
use YooKassa\Model\SupplierInterface;

/**
 * Interface CreateReceiptRequestInterface
 *
 * @package YooKassa
 *
 * @property string $objectId Идентификатор объекта ("payment" или "refund), для которого формируется чек
 * @property string $object_id Идентификатор объекта ("payment" или "refund), для которого формируется чек
 * @property string $type Тип чека в онлайн-кассе: приход "payment" или возврат "refund"
 * @property bool $send Признак отложенной отправки чека
 * @property ReceiptCustomer $customer Информация о плательщике
 * @property int $taxSystemCode Код системы налогообложения. Число 1-6
 * @property int $tax_system_code Код системы налогообложения. Число 1-6
 * @property ReceiptItemInterface[] $items Список товаров в заказе
 * @property SupplierInterface $supplier Информация о поставщике товара или услуги
 * @property SettlementInterface[] $settlements Массив оплат, обеспечивающих выдачу товара
 */
interface CreatePostReceiptRequestInterface
{
    /**
     * Возвращает идентификатор объекта, для которого формируется чек
     *
     * @return string Идентификатор объекта
     */
    public function getObjectId();

    /**
     * Устанавливает идентификатор объекта, для которого формируется чек
     *
     * @param string $value Идентификатор объекта
     * @return CreatePostReceiptRequestInterface
     */
    public function setObjectId($value);

    /**
     * Возвращает тип чека в онлайн-кассе
     *
     * @return string Тип чека в онлайн-кассе: приход "payment" или возврат "refund"
     */
    public function getType();

    /**
     * Устанавливает тип чека в онлайн-кассе
     *
     * @param string $value Тип чека в онлайн-кассе: приход "payment" или возврат "refund"
     * @return CreatePostReceiptRequestInterface
     */
    public function setType($value);

    /**
     * Возвращает признак отложенной отправки чека
     *
     *  @return bool Признак отложенной отправки чека
     */
    public function getSend();

    /**
     * Устанавливает признак отложенной отправки чека
     *
     * @param bool $value Признак отложенной отправки чека
     * @return CreatePostReceiptRequestInterface
     */
    public function setSend($value);

    /**
     * Возвращает код системы налогообложения
     *
     *  @return int Код системы налогообложения. Число 1-6
     */
    public function getTaxSystemCode();

    /**
     * Устанавливает код системы налогообложения
     *
     * @param int $value Код системы налогообложения. Число 1-6
     * @return CreatePostReceiptRequestInterface
     */
    public function setTaxSystemCode($value);

    /**
     * Возвращает информацию о плательщике.
     *
     * @return ReceiptCustomerInterface Информация о плательщике
     */
    public function getCustomer();

    /**
     * Устанавливает информацию о пользователе
     *
     * @param ReceiptCustomerInterface $value Информация о плательщике
     * @return CreatePostReceiptRequestInterface
     */
    public function setCustomer($value);

    /**
     * Возвращает список товаров в заказе
     *
     *  @return ReceiptItemInterface[]
     */
    public function getItems();

    /**
     * Устанавливает список товаров чека
     *
     * @param ReceiptItemInterface[]|array $value Список товаров чека
     * @return CreatePostReceiptRequestInterface
     */
    public function setItems($value);

    /**
     * Добавляет товар в чек
     *
     * @param ReceiptItemInterface|array $value Информация о товаре
     * @return CreatePostReceiptRequestInterface
     */
    public function addItem($value);

    /**
     * Возвращает Массив оплат, обеспечивающих выдачу товара
     *
     *  @return SettlementInterface[]
     */
    public function getSettlements();

    /**
     * Устанавливает массив оплат, обеспечивающих выдачу товара
     *
     * @param SettlementInterface[]|array $value Массив оплат, обеспечивающих выдачу товара
     * @return CreatePostReceiptRequestInterface
     */
    public function setSettlements($value);

    /**
     * Возвращает идентификатор магазина, от имени которого нужно отправить чек
     * @return string|null Идентификатор магазина, от имени которого нужно отправить чек
     */
    public function getOnBehalfOf();

    /**
     * Устанавливает идентификатор магазина, от имени которого нужно отправить чек.
     * Выдается ЮKassa, отображается в разделе Продавцы личного кабинета (столбец shopId).
     * Необходимо передавать, если вы используете решение ЮKassa для платформ.
     *
     * @param string $value Идентификатор магазина, от имени которого нужно отправить чек
     */
    public function setOnBehalfOf($value);

    /**
     * Проверяет есть ли в чеке хотя бы одна позиция
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    function notEmpty();
}
