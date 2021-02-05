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

namespace YooKassa\Request\Payments;

use YooKassa\Common\AbstractPaymentRequest;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AirlineInterface;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Payment;
use YooKassa\Model\PaymentData\AbstractPaymentData;
use YooKassa\Model\ConfirmationAttributes\AbstractConfirmationAttributes;
use YooKassa\Model\Metadata;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\RecipientInterface;

/**
 * Класс объекта запроса к API на проведение нового платежа
 *
 * @package YooKassa\Request\Payments
 *
 * @property RecipientInterface $recipient Получатель платежа, если задан
 * @property AmountInterface $amount Сумма создаваемого платежа
 * @property string $description Описание транзакции
 * @property ReceiptInterface $receipt Данные фискального чека 54-ФЗ
 * @property string $paymentToken Одноразовый токен для проведения оплаты, сформированный YooKassa JS widget
 * @property string $payment_token Одноразовый токен для проведения оплаты, сформированный YooKassa JS widget
 * @property string $paymentMethodId Идентификатор записи о сохраненных платежных данных покупателя
 * @property string $payment_method_id Идентификатор записи о сохраненных платежных данных покупателя
 * @property AbstractPaymentData $paymentMethodData Данные используемые для создания метода оплаты
 * @property AbstractPaymentData $payment_method_data Данные используемые для создания метода оплаты
 * @property AbstractConfirmationAttributes $confirmation Способ подтверждения платежа
 * @property bool $savePaymentMethod Сохранить платежные данные для последующего использования. Значение true
 * инициирует создание многоразового payment_method.
 * @property bool $save_payment_method Сохранить платежные данные для последующего использования. Значение true
 * инициирует создание многоразового payment_method.
 * @property bool $capture Автоматически принять поступившую оплату
 * @property string $clientIp IPv4 или IPv6-адрес покупателя. Если не указан, используется IP-адрес TCP-подключения.
 * @property string $client_ip IPv4 или IPv6-адрес покупателя. Если не указан, используется IP-адрес TCP-подключения.
 * @property Metadata $metadata Метаданные привязанные к платежу
 */
class CreatePaymentRequest extends AbstractPaymentRequest implements CreatePaymentRequestInterface
{
    const MAX_LENGTH_PAYMENT_TOKEN = 10240;

    /**
     * @var RecipientInterface Получатель платежа
     */
    private $_recipient;

    /**
     * @var string Описание транзакции
     */
    private $_description;

    /**
     * @var string Одноразовый токен для проведения оплаты, сформированный YooKassa JS widget
     */
    private $_paymentToken;

    /**
     * @var string Идентификатор записи о сохраненных платежных данных покупателя
     */
    private $_paymentMethodId;

    /**
     * @var AbstractPaymentData Данные используемые для создания метода оплаты
     */
    private $_paymentMethodData;

    /**
     * @var AbstractConfirmationAttributes Способ подтверждения платежа
     */
    private $_confirmation;

    /**
     * @var bool Сохранить платежные данные для последующего использования. Значение true инициирует создание многоразового payment_method.
     */
    private $_savePaymentMethod;

    /**
     * @var bool Автоматически принять поступившую оплату
     */
    private $_capture;

    /**
     * @var string IPv4 или IPv6-адрес покупателя. Если не указан, используется IP-адрес TCP-подключения.
     */
    private $_clientIp;

    /**
     * @var AirlineInterface Объект с данными для продажи авиабилетов
     */
    private $_airline;

    /**
     * @var Metadata Метаданные привязанные к платежу
     */
    private $_metadata;

    /**
     * Возвращает объект получателя платежа
     * @return RecipientInterface|null Объект с информацией о получателе платежа или null если получатель не задан
     */
    public function getRecipient()
    {
        return $this->_recipient;
    }

    /**
     * Проверяет наличие получателя платежа в запросе
     * @return bool True если получатель платежа задан, false если нет
     */
    public function hasRecipient()
    {
        return !empty($this->_recipient);
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
            if ($length > Payment::MAX_LENGTH_DESCRIPTION) {
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
     * Проверяет наличие описания транзакции в создаваемом платеже
     * @return bool True если описание транзакции есть, false если нет
     */
    public function hasDescription()
    {
        return $this->_description !== null;
    }

    /**
     * Устанавливает объект с информацией о получателе платежа
     * @param RecipientInterface|null $value Инстанс объекта информации о получателе платежа или null
     */
    public function setRecipient($value)
    {
        if ($value === null || $value === '') {
            $this->_recipient = null;
        } elseif (is_object($value) && $value instanceof RecipientInterface) {
            $this->_recipient = $value;
        } else {
            throw new \InvalidArgumentException('Invalid recipient value type');
        }
    }

    /**
     * Возвращает одноразовый токен для проведения оплаты
     * @return string Одноразовый токен для проведения оплаты, сформированный YooKassa JS widget
     */
    public function getPaymentToken()
    {
        return $this->_paymentToken;
    }

    /**
     * Проверяет наличие одноразового токена для проведения оплаты
     * @return bool True если токен установлен, false если нет
     */
    public function hasPaymentToken()
    {
        return !empty($this->_paymentToken);
    }

    /**
     * Устанавливает одноразовый токен для проведения оплаты, сформированный YooKassa JS widget
     * @param string $value Одноразовый токен для проведения оплаты
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение превышает допустимую длину
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setPaymentToken($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentToken = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > self::MAX_LENGTH_PAYMENT_TOKEN) {
                throw new InvalidPropertyValueException(
                    'Invalid paymentToken value', 0, 'CreatePaymentRequest.paymentToken', $value
                );
            }
            $this->_paymentToken = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid paymentToken value type', 0, 'CreatePaymentRequest.paymentToken', $value
            );
        }
    }

    /**
     * Устанавливает идентификатор закиси платёжных данных покупателя
     * @return string Идентификатор записи о сохраненных платежных данных покупателя
     */
    public function getPaymentMethodId()
    {
        return $this->_paymentMethodId;
    }

    /**
     * Проверяет наличие идентификатора записи о платёжных данных покупателя
     * @return bool True если идентификатор задан, false если нет
     */
    public function hasPaymentMethodId()
    {
        return !empty($this->_paymentMethodId);
    }

    /**
     * Устанавливает идентификатор записи о сохранённых данных покупателя
     * @param string $value Идентификатор записи о сохраненных платежных данных покупателя
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданные значение не является строкой или null
     */
    public function setPaymentMethodId($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentMethodId = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_paymentMethodId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid paymentMethodId value type in CreatePaymentRequest',
                0,
                'CreatePaymentRequest.CreatePaymentRequest',
                $value
            );
        }
    }

    /**
     * Возвращает данные для создания метода оплаты
     * @return AbstractPaymentData Данные используемые для создания метода оплаты
     */
    public function getPaymentMethodData()
    {
        return $this->_paymentMethodData;
    }

    /**
     * Проверяет установлен ли объект с методом оплаты
     * @return bool True если объект метода оплаты установлен, false если нет
     */
    public function hasPaymentMethodData()
    {
        return !empty($this->_paymentMethodData);
    }

    /**
     * Устанавливает объект с информацией для создания метода оплаты
     * @param AbstractPaymentData|null $value Объект с создания метода оплаты или null
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан объект невалидного типа
     */
    public function setPaymentMethodData($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentMethodData = null;
        } elseif (is_object($value) && $value instanceof AbstractPaymentData) {
            $this->_paymentMethodData = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid paymentMethodData value type in CreatePaymentRequest',
                0,
                'CreatePaymentRequest.paymentMethodData',
                $value
            );
        }
    }

    /**
     * Возвращает способ подтверждения платежа
     * @return AbstractConfirmationAttributes Способ подтверждения платежа
     */
    public function getConfirmation()
    {
        return $this->_confirmation;
    }

    /**
     * Проверяет был ли установлен способ подтверждения платежа
     * @return bool True если способ подтверждения платежа был установлен, false если нет
     */
    public function hasConfirmation()
    {
        return $this->_confirmation !== null;
    }

    /**
     * Устанавливает способ подтверждения платежа
     * @param AbstractConfirmationAttributes|null $value Способ подтверждения платежа
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является объектом типа
     * AbstractConfirmationAttributes или null
     */
    public function setConfirmation($value)
    {
        if ($value === null || $value === '') {
            $this->_confirmation = null;
        } elseif (is_object($value) && $value instanceof AbstractConfirmationAttributes) {
            $this->_confirmation = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid confirmation value type in CreatePaymentRequest',
                0,
                'CreatePaymentRequest.confirmation',
                $value
            );
        }
    }

    /**
     * Возвращает флаг сохранения платёжных данных
     * @return bool Флаг сохранения платёжных данных
     */
    public function getSavePaymentMethod()
    {
        return $this->_savePaymentMethod;
    }

    /**
     * Проверяет был ли установлен флаг сохранения платёжных данных
     * @return bool True если флыг был установлен, false если нет
     */
    public function hasSavePaymentMethod()
    {
        return $this->_savePaymentMethod !== null;
    }

    /**
     * Устанавливает флаг сохранения платёжных данных. Значение true инициирует создание многоразового payment_method.
     * @param bool $value Сохранить платежные данные для последующего использования
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не кастится в bool
     */
    public function setSavePaymentMethod($value)
    {
        if ($value === null || $value === '') {
            $this->_savePaymentMethod = null;
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_savePaymentMethod = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid savePaymentMethod value type in CreatePaymentRequest',
                0,
                'CreatePaymentRequest.savePaymentMethod',
                $value
            );
        }
    }

    /**
     * Возвращает флаг автоматического принятия поступившей оплаты
     * @return bool True если требуется автоматически принять поступившую оплату, false если нет
     */
    public function getCapture()
    {
        return $this->_capture;
    }

    /**
     * Проверяет был ли установлен флаг автоматического приняти поступившей оплаты
     * @return bool True если флаг автоматического принятия оплаты был установлен, false если нет
     */
    public function hasCapture()
    {
        return $this->_capture !== null;
    }

    /**
     * Устанавливает флаг автоматического принятия поступившей оплаты
     * @param bool $value Автоматически принять поступившую оплату
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не кастится в bool
     */
    public function setCapture($value)
    {
        if ($value === null || $value === '') {
            $this->_capture = null;
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_capture = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid capture value type in CreatePaymentRequest', 0, 'CreatePaymentRequest.capture', $value
            );
        }
    }

    /**
     * Возвращает IPv4 или IPv6-адрес покупателя
     * @return string IPv4 или IPv6-адрес покупателя
     */
    public function getClientIp()
    {
        return $this->_clientIp;
    }

    /**
     * Проверяет был ли установлен IPv4 или IPv6-адрес покупателя
     * @return bool True если IP адрес покупателя был установлен, false если нет
     */
    public function hasClientIp()
    {
        return $this->_clientIp !== null;
    }

    /**
     * Устанавливает IP адрес покупателя
     * @param string $value IPv4 или IPv6-адрес покупателя
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не является строкой
     */
    public function setClientIp($value)
    {
        if ($value === null || $value === '') {
            $this->_clientIp = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_clientIp = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid clientIp value type in CreatePaymentRequest', 0, 'CreatePaymentRequest.clientIp', $value
            );
        }
    }

    /**
     * @return AirlineInterface
     */
    public function getAirline()
    {
        return $this->_airline;
    }


    /**
     * @param AirlineInterface $value
     */
    public function setAirline(AirlineInterface $value)
    {
        $this->_airline = $value;
    }

    /**
     * Проверяет были ли установлены данные длинной записи
     * @return bool
     */
    function hasAirline()
    {
        return $this->_airline !== null;
    }

    /**
     * Возвращает данные оплаты установленные мерчантом
     * @return Metadata Метаданные, привязанные к платежу
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Проверяет были ли установлены метаданные заказа
     * @return bool True если метаданные были установлены, false если нет
     */
    public function hasMetadata()
    {
        return !empty($this->_metadata) && $this->_metadata->count() > 0;
    }

    /**
     * Устанавливает метаданные, привязанные к платежу
     * @param Metadata|array|null $value Метаданные платежа, устанавливаемые мерчантом
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданные данные не удалось интерпретировать как
     * метаданные платежа
     */
    public function setMetadata($value)
    {
        if ($value === null || (is_array($value) && empty($value))) {
            $this->_metadata = null;
        } elseif (is_object($value) && $value instanceof Metadata) {
            $this->_metadata = $value;
        } elseif (is_array($value)) {
            $this->_metadata = new Metadata();
            foreach ($value as $key => $val) {
                $this->_metadata->offsetSet($key, (string)$val);
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid metadata value type in CreatePaymentRequest', 0, 'CreatePaymentRequest.metadata', $value
            );
        }
    }

    /**
     * Проверяет на валидность текущий объект
     * @return bool True если объект запроса валиден, false если нет
     */
    public function validate()
    {
        if (!parent::validate()) {
            return false;
        }
        if ($this->hasPaymentToken()) {
            if ($this->hasPaymentMethodId()) {
                $this->setValidationError('Both paymentToken and paymentMethodID values are specified');
                return false;
            }
            if ($this->hasPaymentMethodData()) {
                $this->setValidationError('Both paymentToken and paymentData values are specified');
                return false;
            }
        } elseif ($this->hasPaymentMethodId()) {
            if ($this->hasPaymentMethodData()) {
                $this->setValidationError('Both paymentMethodID and paymentData values are specified');
                return false;
            }
        }
        return true;
    }

    /**
     * Возвращает билдер объектов запросов создания платежа
     * @return CreatePaymentRequestBuilder Инстанс билдера объектов запрсов
     */
    public static function builder()
    {
        return new CreatePaymentRequestBuilder();
    }
}
