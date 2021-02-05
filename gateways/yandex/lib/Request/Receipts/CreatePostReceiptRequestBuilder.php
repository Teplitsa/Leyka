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

namespace YooKassa\Request\Receipts;


use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\Exceptions\InvalidRequestException;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptCustomerInterface;
use YooKassa\Model\ReceiptItemInterface;
use YooKassa\Model\SettlementInterface;

class CreatePostReceiptRequestBuilder extends AbstractRequestBuilder
{
    /** @var CreatePostReceiptRequest Собираемый объект запроса */
    protected $currentObject;

    /** @var AmountInterface */
    protected $amount;

    /** @var ReceiptCustomer */
    protected $customer;

    /**
     * Инициализирует объект запроса, который в дальнейшем будет собираться билдером
     * @return CreatePostReceiptRequest Инстанс собираемого объекта запроса к API
     */
    protected function initCurrentObject()
    {
        $this->customer = new ReceiptCustomer();
        $this->amount = new MonetaryAmount();

        return new CreatePostReceiptRequest();
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
        if (is_object($value) && $value instanceof AmountInterface) {
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
     * Устанавливает валюту в которой будет происходить подтверждение оплаты заказа
     *
     * @param string $value Валюта в которой подтверждается оплата
     *
     * @return self Инстанс билдера запросов
     */
    public function setCurrency($value)
    {
        $this->amount->setCurrency($value);
        foreach ($this->currentObject->getItems() as $item) {
            $item->getPrice()->setCurrency($value);
        }

        return $this;
    }

    /**
     * @param ReceiptCustomerInterface|array $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setCustomer($value)
    {
        if (is_array($value)) {
            $this->customer->fromArray($value);
        } elseif ($value instanceof ReceiptCustomerInterface) {
            $this->customer = $value;
        } else {
            $this->customer = null;
        }
        $this->currentObject->setCustomer($this->customer);
        return $this;
    }

    /**
     * @param ReceiptItemInterface[] $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setItems($value)
    {
        $this->currentObject->setItems($value);
        return $this;
    }

    /**
     * @param int $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setTaxSystemCode($value)
    {
        $this->currentObject->setTaxSystemCode($value);
        return $this;
    }

    /**
     * @param string $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setType($value)
    {
        $this->currentObject->setType($value);
        return $this;
    }

    /**
     * @param bool $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setSend($value)
    {
        $this->currentObject->setSend($value);
        return $this;
    }

    /**
     * @param string $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setOnBehalfOf($value)
    {
        $this->currentObject->setOnBehalfOf($value);
        return $this;
    }

    /**
     * @param SettlementInterface[] $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setSettlements($value)
    {
        $this->currentObject->setSettlements($value);
        return $this;
    }

    /**
     * @param string $value
     * @return CreatePostReceiptRequestBuilder
     */
    public function setObjectId($value)
    {
        $this->currentObject->setObjectId($value);
        return $this;
    }

    /**
     * Строит и возвращает объект запроса для отправки в API ЮKassa
     * @param array|null $options Массив параметров для установки в объект запроса
     * @return CreatePostReceiptRequest Инстанс объекта запроса
     *
     * @throws InvalidRequestException Выбрасывается если собрать объект запроса не удалось
     */
    public function build(array $options = null)
    {
        if (!empty($options)) {
            $this->setOptions($options);

            if (!empty($options['payment_id'])) {
                $this->setObjectId($options['payment_id']);
            } elseif (!empty($options['refund_id'])) {
                $this->setObjectId($options['refund_id']);
            }
        }

        return parent::build($options);
    }

}