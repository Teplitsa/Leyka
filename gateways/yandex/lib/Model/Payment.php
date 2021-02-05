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

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentMethod\AbstractPaymentMethod;

/**
 * Payment - Данные о платеже
 *
 * @property string $id Идентификатор платежа
 * @property string $status Текущее состояние платежа
 * @property RecipientInterface $recipient  Получатель платежа
 * @property AmountInterface $amount Сумма заказа
 * @property string $description Описание транзакци
 * @property AbstractPaymentMethod $paymentMethod Способ проведения платежа
 * @property AbstractPaymentMethod $payment_method Способ проведения платежа
 * @property \DateTime $createdAt Время создания заказа
 * @property \DateTime $created_at Время создания заказа
 * @property \DateTime $capturedAt Время подтверждения платежа магазином
 * @property \DateTime $captured_at Время подтверждения платежа магазином
 * @property \DateTime $expiresAt Время, до которого можно бесплатно отменить или подтвердить платеж
 * @property \DateTime $expires_at Время, до которого можно бесплатно отменить или подтвердить платеж
 * @property Confirmation\AbstractConfirmation $confirmation Способ подтверждения платежа
 * @property AmountInterface $refundedAmount Сумма возвращенных средств платежа
 * @property AmountInterface $refunded_amount Сумма возвращенных средств платежа
 * @property bool $paid Признак оплаты заказа
 * @property bool $refundable Возможность провести возврат по API
 * @property string $receiptRegistration Состояние регистрации фискального чека
 * @property string $receipt_registration Состояние регистрации фискального чека
 * @property Metadata $metadata Метаданные платежа указанные мерчантом
 * @property CancellationDetailsInterface $cancellationDetails Комментарий к отмене платежа
 * @property CancellationDetailsInterface $cancellation_details Комментарий к отмене платежа
 * @property AuthorizationDetailsInterface $authorizationDetails Данные об авторизации платежа
 * @property AuthorizationDetailsInterface $authorization_details Данные об авторизации платежа
 * @property TransferInterface[] $transfers Данные о распределении платежа между магазинами
 */
class Payment extends AbstractObject implements PaymentInterface
{
    const MAX_LENGTH_DESCRIPTION = 128;

    /**
     * @var string Идентификатор платежа
     */
    private $_id;

    /**
     * @var string Текущее состояние платежа
     */
    private $_status;

    /**
     * @var RecipientInterface|null Получатель платежа
     */
    private $_recipient;

    /**
     * @var AmountInterface
     */
    private $_amount;

    /**
     * @var string
     */
    private $_description;

    /**
     * @var AbstractPaymentMethod Способ проведения платежа
     */
    private $_paymentMethod;

    /**
     * @var \DateTime Время создания заказа
     */
    private $_createdAt;

    /**
     * @var \DateTime Время подтверждения платежа магазином
     */
    private $_capturedAt;

    /**
     * @var Confirmation\AbstractConfirmation Способ подтверждения платежа
     */
    private $_confirmation;

    /**
     * @var AmountInterface Сумма возвращенных средств платежа
     */
    private $_refundedAmount;

    /**
     * @var bool Признак оплаты заказа
     */
    private $_paid;

    /**
     * @var bool Возможность провести возврат по API
     */
    private $_refundable;

    /**
     * @var string Состояние регистрации фискального чека
     */
    private $_receiptRegistration;

    /**
     * @var Metadata Метаданные платежа указанные мерчантом
     */
    private $_metadata;

    /**
     * Время, до которого можно бесплатно отменить или подтвердить платеж. В указанное время платеж в статусе
     * `waiting_for_capture` будет автоматически отменен.
     *
     * @var \DateTime Время, до которого можно бесплатно отменить или подтвердить платеж
     * @since 1.0.2
     */
    private $_expiresAt;

    /**
     * Комментарий к статусу canceled: кто отменил платеж и по какой причине
     * @var CancellationDetailsInterface
     * @since 1.0.13
     */
    private $_cancellationDetails;

    /**
     * Данные об авторизации платежа
     * @var AuthorizationDetailsInterface
     * @since 1.0.18
     */
    private $_authorizationDetails;

    /**
     * @var TransferInterface[]
     */
    private $_transfers = array();

    /**
     * @var MonetaryAmount
     */
    private $_incomeAmount;

    /**
     * @var RequestorInterface
     */
    private $_requestor;

    /**
     * Признак тестовой операции.
     * @var boolean
     * @since 1.1.3
     */
    private $_test;


    /**
     * Возвращает идентификатор платежа
     * @return string Идентификатор платежа
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор платежа
     * @param string $value Идентификатор платежа
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 36
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setId($value)
    {
        if (TypeCast::canCastToString($value)) {
            $length = mb_strlen($value, 'utf-8');
            if ($length != 36) {
                throw new InvalidPropertyValueException('Invalid payment id value', 0, 'Payment.id', $value);
            }
            $this->_id = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid payment id value type', 0, 'Payment.id', $value);
        }
    }

    /**
     * Возвращает состояние платежа
     * @return string Текущее состояние платежа
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Устанавливает статус платежа
     * @param string $value Статус платежа
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!PaymentStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid payment status value', 0, 'Payment.status', $value);
            }
            $this->_status = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment status value type', 0, 'Payment.status', $value
            );
        }
    }

    /**
     * Возвращает получателя платежа
     * @return RecipientInterface|null Получатель платежа или null если получатель не задан
     */
    public function getRecipient()
    {
        return $this->_recipient;
    }

    /**
     * Устанавливает получателя платежа
     * @param RecipientInterface $value Объект с информацией о получателе платежа
     */
    public function setRecipient(RecipientInterface $value)
    {
        $this->_recipient = $value;
    }

    /**
     * Возвращает сумму
     * @return AmountInterface Сумма платежа
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает сумму платежа
     * @param AmountInterface $value Сумма платежа
     */
    public function setAmount(AmountInterface $value)
    {
        $this->_amount = $value;
    }

    /**
     * Возвращает описание транзакции
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает описание транзакции
     * @param string $value
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение превышает допустимую длину
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setDescription($value)
    {
        if ($value === null || $value === '') {
            $this->_description = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > self::MAX_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'Invalid description value', 0, 'CreatePaymentRequest.description', $value
                );
            }
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid description value type', 0, 'CreatePaymentRequest.description', $value
            );
        }
    }

    /**
     * Возвращает используемый способ проведения платежа
     * @return AbstractPaymentMethod Способ проведения платежа
     */
    public function getPaymentMethod()
    {
        return $this->_paymentMethod;
    }

    /**
     * @param AbstractPaymentMethod $value
     */
    public function setPaymentMethod(AbstractPaymentMethod $value)
    {
        $this->_paymentMethod = $value;
    }

    /**
     * Возвращает время создания заказа
     * @return \DateTime Время создания заказа
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * Устанавливает время создания заказа
     * @param \DateTime|string|int $value Время создания заказа
     *
     * @throws EmptyPropertyValueException Выбрасывается если в метод была передана пустая дата
     * @throws InvalidPropertyValueException Выбрасвается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setCreatedAt($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty created_at value', 0, 'payment.createdAt');
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid created_at value', 0, 'payment.createdAt', $value);
            }
            $this->_createdAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid created_at value', 0, 'payment.createdAt', $value);
        }
    }

    /**
     * Возвращает время подтверждения платежа магазином или null если если время не задано
     * @return \DateTime|null Время подтверждения платежа магазином
     */
    public function getCapturedAt()
    {
        return $this->_capturedAt;
    }

    /**
     * Устанавливает время подтверждения платежа магазином
     * @param \DateTime|string|int|null $value Время подтверждения платежа магазином
     *
     * @throws InvalidPropertyValueException Выбрасвается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setCapturedAt($value)
    {
        if ($value === null || $value === '') {
            $this->_capturedAt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid captured_at value', 0, 'payment.capturedAt', $value);
            }
            $this->_capturedAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid captured_at value', 0, 'payment.capturedAt', $value);
        }
    }

    /**
     * Возвращает способ подтверждения платежа
     * @return Confirmation\AbstractConfirmation Способ подтверждения платежа
     */
    public function getConfirmation()
    {
        return $this->_confirmation;
    }

    /**
     * Устанавливает способ подтверждения платежа
     * @param Confirmation\AbstractConfirmation $value Способ подтверждения платежа
     */
    public function setConfirmation(Confirmation\AbstractConfirmation $value)
    {
        $this->_confirmation = $value;
    }

    /**
     * Возвращает сумму возвращенных средств
     * @return AmountInterface Сумма возвращенных средств платежа
     */
    public function getRefundedAmount()
    {
        return $this->_refundedAmount;
    }

    /**
     * Устанавливает сумму возвращенных средств
     * @param AmountInterface $value Сумма возвращенных средств платежа
     */
    public function setRefundedAmount(AmountInterface $value)
    {
        $this->_refundedAmount = $value;
    }

    /**
     * Проверяет был ли уже оплачен заказ
     * @return bool Признак оплаты заказа, true если заказ оплачен, false если нет
     */
    public function getPaid()
    {
        return $this->_paid;
    }

    /**
     * Устанавливает флаг оплаты заказа
     * @param bool $value Признак оплаты заказа
     *
     * @throws EmptyPropertyValueException Выбрасывается если переданный аргумент пуст
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не кастится в булево значение
     */
    public function setPaid($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty payment paid flag value', 0, 'Payment.paid');
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_paid = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment paid flag value type', 0, 'Payment.paid', $value
            );
        }
    }

    /**
     * Проверяет возможность провести возврат по API
     * @return bool Возможность провести возврат по API, true если есть, false если нет
     */
    public function getRefundable()
    {
        return $this->_refundable;
    }

    /**
     * Устанавливает возможность провести возврат по API
     * @param bool $value Возможность провести возврат по API
     *
     * @throws EmptyPropertyValueException Выбрасывается если переданный аргумент пуст
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не кастится в булево значение
     */
    public function setRefundable($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty payment refundable flag value', 0, 'Payment.refundable');
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_refundable = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment refundable flag value type', 0, 'Payment.refundable', $value
            );
        }
    }

    /**
     * Возвращает состояние регистрации фискального чека
     * @return string Состояние регистрации фискального чека
     */
    public function getReceiptRegistration()
    {
        return $this->_receiptRegistration;
    }

    /**
     * Устанавливает состояние регистрации фискального чека
     * @param string $value Состояние регистрации фискального чека
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное состояние регистрации не существует
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не строка
     */
    public function setReceiptRegistration($value)
    {
        if ($value === null || $value === '') {
            $this->_receiptRegistration = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (ReceiptRegistrationStatus::valueExists($value)) {
                $this->_receiptRegistration = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid receipt_registration value', 0, 'payment.receiptRegistration', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid receipt_registration value type', 0, 'payment.receiptRegistration', $value
            );
        }
    }

    /**
     * Возвращает метаданные платежа установленные мерчантом
     * @return Metadata Метаданные платежа указанные мерчантом
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Устанавливает метаданные платежа
     * @param Metadata $value Метаданные платежа указанные мерчантом
     */
    public function setMetadata(Metadata $value)
    {
        $this->_metadata = $value;
    }

    /**
     * Возвращает время до которого можно бесплатно отменить или подтвердить платеж или null если оно не задано
     * @return \DateTime|null Время, до которого можно бесплатно отменить или подтвердить платеж
     *
     * @since 1.0.2
     */
    public function getExpiresAt()
    {
        return $this->_expiresAt;
    }

    /**
     * Устанавливает время до которого можно бесплатно отменить или подтвердить платеж
     * @param \DateTime|string|int|null $value Время, до которого можно бесплатно отменить или подтвердить платеж
     *
     * @throws InvalidPropertyValueException Выбрасывается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     *
     * @since 1.0.2
     */
    public function setExpiresAt($value)
    {
        if ($value === null || $value === '') {
            $this->_expiresAt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid expires_at value', 0, 'payment.expires_at', $value);
            }
            $this->_expiresAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid expires_at value', 0, 'payment.expires_at', $value);
        }
    }

    /**
     * Возвращает комментарий к статусу canceled: кто отменил платеж и по какой причине
     * @return CancellationDetailsInterface|null Комментарий к статусу canceled
     * @since 1.0.13
     */
    public function getCancellationDetails()
    {
        return $this->_cancellationDetails;
    }

    /**
     * Устанавливает комментарий к статусу canceled: кто отменил платеж и по какой причине
     * @param CancellationDetailsInterface $value Комментарий к статусу canceled
     */
    public function setCancellationDetails(CancellationDetailsInterface $value)
    {
        $this->_cancellationDetails = $value;
    }
    /**
     * Возвращает данные об авторизации платежа
     * @return AuthorizationDetailsInterface|null Данные об авторизации платежа
     * @since 1.0.18
     */
    public function getAuthorizationDetails()
    {
        return $this->_authorizationDetails;
    }

    /**
     * Устанавливает данные об авторизации платежа
     * @param AuthorizationDetailsInterface $value Данные об авторизации платежа
     */
    public function setAuthorizationDetails(AuthorizationDetailsInterface $value)
    {
        $this->_authorizationDetails = $value;
    }

    /**
     * Устанавливает transfers (массив распределения денег между магазинами)
     * @param $value
     */
    public function setTransfers($value)
    {
        if (!is_array($value)) {
            $message = 'Transfers must be an array of TransferInterface';
            throw new InvalidPropertyValueTypeException($message, 0, 'Payment.transfers', $value);
        }

        foreach ($value as $item) {
            if (!($item instanceof TransferInterface)) {
                $message = 'Transfers must be an array of TransferInterface';
                throw new InvalidPropertyValueTypeException($message, 0, 'Payment.transfers', $value);
            }
        }

        $this->_transfers = $value;
    }

    public function getTransfers()
    {
        return $this->_transfers;
    }

    /**
     * @param MonetaryAmount $amount
     */
    public function setIncomeAmount(MonetaryAmount $amount)
    {
        $this->_incomeAmount = $amount;
    }

    public function getIncomeAmount()
    {
        return $this->_incomeAmount;
    }

    /**
     * @param $value
     */
    public function setRequestor($value)
    {
        if (is_array($value)) {
            $value = new Requestor($value);
        }

        if (!($value instanceof RequestorInterface)) {
            throw new InvalidPropertyValueTypeException('Invalid Requestor type', 0, 'Payment.requestor', $value);
        }

        $this->_requestor = $value;
    }

    /**
     * @return RequestorInterface
     */
    public function getRequestor()
    {
        return $this->_requestor;
    }

    /**
     * @return bool
     */
    public function getTest()
    {
        return $this->_test;
    }

    /**
     * @param bool $test
     */
    public function setTest($test)
    {
        if ($test === null || $test === '') {
            throw new EmptyPropertyValueException('Empty payment test flag value', 0, 'Payment.test');
        } elseif (TypeCast::canCastToBoolean($test)) {
            $this->_test = (bool)$test;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment test flag value type', 0, 'Payment.test', $test
            );
        }
    }
}