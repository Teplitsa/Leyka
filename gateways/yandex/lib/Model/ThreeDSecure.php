<?php

namespace YooKassa\Model;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * ThreeDSecure - Данные о прохождении пользователем аутентификации по 3‑D Secure для подтверждения платежа.
 *
 * @property bool $applied Отображение пользователю формы для прохождения аутентификации по 3‑D Secure.
 */
class ThreeDSecure extends AbstractObject
{
    /**
     * @var bool|null Отображение пользователю формы для прохождения аутентификации по 3‑D Secure.
     */
    private $_applied;

    /**
     * Возвращает признак отображения пользователю формы для прохождения аутентификации по 3‑D Secure
     *
     * @return bool|null Признак отображения пользователю формы для прохождения аутентификации по 3‑D Secure
     */
    public function getApplied()
    {
        return $this->_applied;
    }

    /**
     * Устанавливает признак отображения пользователю формы для прохождения аутентификации по 3‑D Secure
     *
     * @param bool $value Данные о прохождении аутентификации по 3‑D Secure
     *
     * @throws InvalidPropertyValueTypeException
     */
    public function setApplied($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "applied" parameter in ThreeDSecure',
                0,
                'authorization_details.three_d_secure.applied'
            );
        }

        if (!TypeCast::canCastToBoolean($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid applied value type',
                0,
                'authorization_details.three_d_secure.applied',
                $value
            );
        }

        $this->_applied = (bool)$value;
    }
}