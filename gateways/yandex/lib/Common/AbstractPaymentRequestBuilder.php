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

namespace YooKassa\Common;

use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Deal\PaymentDealInfo;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\Receipt;
use YooKassa\Model\Receipt\ReceiptItemAmount;
use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\ReceiptItem;
use YooKassa\Model\ReceiptItemInterface;
use YooKassa\Model\Transfer;
use YooKassa\Model\TransferInterface;

/**
 * Базовый класс объекта платежного запроса, передаваемого в методы клиента API
 *
 * @package YooKassa
 *
 * @since 1.0.18
 */
abstract class AbstractPaymentRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Сумма
     * @var MonetaryAmount
     */
    protected $amount;

    /**
     * Объект с информацией о чеке
     * @var Receipt
     */
    protected $receipt;

    /**
     * Массив платежей в пользу разных мерчантов
     * @var TransferInterface[]
     */
    protected $transfers;

    /**
     * @return self
     */
    protected function initCurrentObject()
    {
        $this->amount    = new MonetaryAmount();
        $this->receipt   = new Receipt();
        $this->transfers = array();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function build(array $options = null)
    {
        return parent::build($options);
    }

    /**
     * Устанавливает сумму
     *
     * @param AmountInterface|array|string $value Сумма оплаты
     *
     * @return self Инстанс билдера запросов
     */
    public function setAmount($value)
    {
        if ($value === null || $value === '') {
            $this->amount = new MonetaryAmount();
        } elseif ($value instanceof AmountInterface) {
            $this->amount->setValue($value->getValue());
            $this->amount->setCurrency($value->getCurrency());
        } elseif (is_array($value)) {
            $this->amount->fromArray($value);
        } else {
            $this->amount->setValue($value);
        }

        return $this;
    }

    /**
     * Устанавливает трансферы
     *
     * @param TransferInterface[]|array|null $value Массив трансферов
     *
     * @return self Инстанс билдера запросов
     */
    public function setTransfers($value)
    {
        if (is_array($value)) {
            $this->transfers = array();
            foreach ($value as $item) {
                if ($item instanceof TransferInterface) {
                    $this->transfers[] = $item;
                } elseif (is_array($item)) {
                    $this->transfers[] = new Transfer($item);
                }
            }
        } else {
            $this->transfers = array();
        }

        return $this;
    }

    /**
     * Устанавливает валюту в которой будет происходить подтверждение оплаты заказа
     *
     * @param string $value Валюта в которой подтверждается оплата
     *
     * @return self Инстанс билдера запросов
     */
    public function setCurrency($value)
    {
        $this->amount->setCurrency($value);
        foreach ($this->receipt->getItems() as $item) {
            $item->getPrice()->setCurrency($value);
        }

        return $this;
    }

    /**
     * Устанавливает чек
     *
     * @param ReceiptInterface|array $value Инстанс чека или ассоциативный массив с данными чека
     *
     * @return self
     *
     * @throws InvalidPropertyValueTypeException Генерируется если было передано значение невалидного типа
     */
    public function setReceipt($value)
    {
        if (is_array($value)) {
            $this->receipt->fromArray($value);
        } elseif ($value instanceof ReceiptInterface) {
            $this->receipt = clone $value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid receipt value type', 0, 'receipt', $value);
        }

        return $this;
    }

    /**
     * Устанавлвиает список товаров для создания чека
     *
     * @param array $value Массив товаров в заказе
     *
     * @return self Инстанс билдера запросов
     *
     * @throws InvalidPropertyValueException Выбрасывается если хотя бы один из товаров имеет неверную структуру
     */
    public function setReceiptItems($value)
    {
        $this->receipt->setItems(array());
        $index = 0;
        foreach ($value as $item) {
            if ($item instanceof ReceiptItemInterface) {
                $this->receipt->addItem($item);
            } else {
                if (empty($item['title']) && empty($item['description'])) {
                    throw new InvalidPropertyValueException(
                        'Item#'.$index.' title or description not specified',
                        0,
                        'AbstractPaymentRequestBuilder.items['.$index.'].title',
                        json_encode($item)
                    );
                }
                foreach (array('price', 'quantity', 'vatCode') as $property) {
                    if (empty($item[$property])) {
                        throw new InvalidPropertyValueException(
                            'Item#'.$index.' '.$property.' not specified',
                            0,
                            'AbstractPaymentRequestBuilder.items['.$index.'].'.$property,
                            json_encode($item)
                        );
                    }
                }
                $this->addReceiptItem(
                    empty($item['title']) ? $item['description'] : $item['title'],
                    $item['price'],
                    $item['quantity'],
                    $item['vatCode'],
                    isset($item['payment_mode']) ? $item['payment_mode'] : null,
                    isset($item['payment_subject']) ? $item['payment_subject'] : null,
                    isset($item['product_code']) ? $item['product_code'] : null,
                    isset($item['country_of_origin_code']) ? $item['country_of_origin_code'] : null,
                    isset($item['customs_declaration_number']) ? $item['customs_declaration_number'] : null,
                    isset($item['excise']) ? $item['excise'] : null
                );
            }
            $index++;
        }

        return $this;
    }

    /**
     * Добавляет в чек товар
     *
     * @param string $title Название или описание товара
     * @param string $price Цена товара в валюте, заданной в заказе
     * @param float $quantity Количество товара
     * @param int $vatCode Ставка НДС
     *
     * @param null|string $paymentSubject значение перечисления PaymentSubject
     * @see \YooKassa\Model\Receipt\PaymentSubject::class
     *
     * @param null|string $paymentMode значение перечисления PaymentMode
     * @see \YooKassa\Model\Receipt\PaymentMode::class
     *
     * @return self Инстанс билдера запросов
     */
    public function addReceiptItem($title, $price, $quantity, $vatCode, $paymentMode = null, $paymentSubject = null, $productCode = null, $countryOfOriginCode = null, $customsDeclarationNumber = null, $excise = null)
    {
        $item = new ReceiptItem();
        $item->setDescription($title);
        $item->setQuantity($quantity);
        $item->setVatCode($vatCode);
        $item->setPrice(new ReceiptItemAmount($price, $this->amount->getCurrency()));
        $item->setPaymentSubject($paymentSubject);
        $item->setPaymentMode($paymentMode);
        $item->setProductCode($productCode);
        $item->setCountryOfOriginCode($countryOfOriginCode);
        $item->setCustomsDeclarationNumber($customsDeclarationNumber);
        $item->setExcise($excise);
        $this->receipt->addItem($item);

        return $this;
    }

    /**
     * Добавляет в чек доставку товара
     *
     * @param string $title Название доставки в чеке
     * @param string $price Стоимость доставки
     * @param int $vatCode Ставка НДС
     *
     * @param null|string $paymentSubject значение перечисления PaymentSubject
     * @see \YooKassa\Model\Receipt\PaymentSubject::class
     *
     * @param null|string $paymentMode значение перечисления PaymentMode
     * @see \YooKassa\Model\Receipt\PaymentMode::class
     *
     * @return self Инстанс билдера запросов
     */
    public function addReceiptShipping($title, $price, $vatCode, $paymentMode = null, $paymentSubject = null)
    {
        $item = new ReceiptItem();
        $item->setDescription($title);
        $item->setQuantity(1);
        $item->setVatCode($vatCode);
        $item->setIsShipping(true);
        $item->setPrice(new ReceiptItemAmount($price, $this->amount->getCurrency()));
        $item->setPaymentMode($paymentMode);
        $item->setPaymentSubject($paymentSubject);
        $this->receipt->addItem($item);

        return $this;
    }

    /**
     * Устанавливает адрес электронной почты получателя чека
     *
     * @param string $value Email получателя чека
     *
     * @return self Инстанс билдера запросов
     */
    public function setReceiptEmail($value)
    {
        if (!$this->receipt->getCustomer()) {
            $this->receipt->setCustomer(new ReceiptCustomer());
        }
        $this->receipt->getCustomer()->setEmail($value);

        return $this;
    }

    /**
     * Устанавливает телефон получателя чека
     *
     * @param string $value Телефон получателя чека
     * @return self Инстанс билдера запросов
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     */
    public function setReceiptPhone($value)
    {
        if (!$this->receipt->getCustomer()) {
            $this->receipt->setCustomer(new ReceiptCustomer());
        }
        $this->receipt->getCustomer()->setPhone($value);

        return $this;
    }

    /**
     * Устанавливает код системы налогообложения.
     *
     * @param int $value Код системы налогообложения. Число 1-6.
     *
     * @return self Инстанс билдера запросов
     */
    public function setTaxSystemCode($value)
    {
        $this->receipt->setTaxSystemCode($value);

        return $this;
    }

}
