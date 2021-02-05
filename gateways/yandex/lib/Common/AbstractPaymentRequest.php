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

namespace YooKassa\Common;

use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Receipt;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\Transfer;
use YooKassa\Model\TransferInterface;

/**
 * Класс объекта запроса к API
 *
 * @property AmountInterface $amount Сумма
 * @property ReceiptInterface $receipt Данные фискального чека 54-ФЗ
 * @property TransferInterface[] $transfers Данные о распределении платежа между магазинами
 *
 * @since 1.0.18
 */
class AbstractPaymentRequest extends AbstractRequest
{
    /**
     * @var AmountInterface Сумма оплаты
     */
    protected $_amount;

    /**
     * @var Receipt Данные фискального чека 54-ФЗ
     */
    protected $_receipt;

    /**
     * @var TransferInterface[]
     */
    protected $_transfers = array();

    /**
     * Возвращает сумму оплаты
     * @return AmountInterface Сумма оплаты
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Проверяет была ли установлена сумма оплаты
     * @return bool True если сумма оплаты была установлена, false если нет
     */
    public function hasAmount()
    {
        return !empty($this->_amount);
    }

    /**
     * Устанавливает сумму оплаты
     * @param AmountInterface $value Сумма оплаты
     */
    public function setAmount(AmountInterface $value)
    {
        $this->_amount = $value;
    }

    /**
     * Возвращает чек, если он есть
     * @return ReceiptInterface|null Данные фискального чека 54-ФЗ или null если чека нет
     */
    public function getReceipt()
    {
        return $this->_receipt;
    }

    /**
     * Устанавливает чек
     * @param ReceiptInterface|null $value Инстанс чека или null для удаления информации о чеке
     * @throws InvalidPropertyValueTypeException Выбрасывается если передан не инстанс класса чека и не null
     */
    public function setReceipt($value)
    {
        if ($value === null || $value instanceof ReceiptInterface) {
            $this->_receipt = $value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid receipt in Refund', 0, 'Refund.receipt', $value);
        }
    }

    /**
     * Проверяет наличие чека
     * @return bool True если чек есть, false если нет
     */
    public function hasReceipt()
    {
        return $this->_receipt !== null && $this->_receipt->notEmpty();
    }

    /**
     * Удаляет чек из запроса
     */
    public function removeReceipt()
    {
        $this->_receipt = null;
    }

    /**
     * Устанавливает transfers (массив распределения денег между магазинами)
     * @param TransferInterface[]|array $value
     */
    public function setTransfers($value)
    {
        if (!is_array($value)) {
            $message = 'Transfers must be an array of TransferInterface';
            throw new InvalidPropertyValueTypeException($message, 0, 'Payment.transfers', $value);
        }

        $transfers = array();
        foreach ($value as $item) {
            if (is_array($item)) {
                $item = new Transfer($item);
            }

            if (!($item instanceof TransferInterface)) {
                $message = 'Transfers must be an array of TransferInterface';
                throw new InvalidPropertyValueTypeException($message, 0, 'Payment.transfers', $value);
            }

            $transfers[] = $item;
        }

        $this->_transfers = $transfers;
    }

    /**
     * Валидирует объект запроса
     * @return bool True если запрос валиден и его можно отправить в API, false если нет
     */
    public function validate()
    {
        if ($this->_amount === null) {
            $this->setValidationError('Payment amount not specified');
            return false;
        }

        $value = $this->_amount->getValue();
        if (empty($value) || $value <= 0.0) {
            $this->setValidationError('Invalid payment amount value: ' . $value);

            return false;
        }

        if (!empty($this->_transfers)) {
            $sum = 0;
            foreach ($this->_transfers as $transfer) {
                if ($transfer->getAmount() === null) {
                    $this->setValidationError('Payment amount not specified');
                    return false;
                }

                $value = $transfer->getAmount()->getValue();
                if (empty($value) || $value <= 0.0) {
                    $this->setValidationError('Invalid transfer amount value: ' . $value);
                    return false;
                }
                $sum += (float) $value;

                $accountId = $transfer->getAccountId();
                if (empty($accountId)) {
                    $this->setValidationError('Transfer account id not specified');
                    return false;
                }
            }

            if ($sum !== (float) $this->getAmount()->getValue()) {
                $this->setValidationError('Transfer amount sum does not match top-level amount');
            }
        }

        if ($this->getReceipt() && $this->getReceipt()->notEmpty()) {
            $email = $this->getReceipt()->getCustomer()->getEmail();
            $phone = $this->getReceipt()->getCustomer()->getPhone();
            if (empty($email) && empty($phone)) {
                $this->setValidationError('Both email and phone values are empty in receipt');
                return false;
            }
        }

        return true;
    }

    public function hasTransfers()
    {
        return !empty($this->_transfers);
    }

    public function getTransfers()
    {
        return $this->_transfers;
    }
}