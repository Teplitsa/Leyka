<?php


namespace YooKassa\Model;


use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Инициатор платежа или возврата.
 *
 * Инициатором может быть магазин, подключенный к ЮKassa, `merchant` или приложение, которому владелец магазина
 * [разрешил](https://yookassa.ru/developers/partners-api/basics) совершать операции от своего имени `third_party_client`.
 *
 * @package YooKassa
 * @deprecated Не используется. Будет удален в следующих версиях
 *
 * @property string $type Тип инициатора
 * @property string $accountId Идентификатор магазина
 * @property string $account_id Идентификатор магазина
 * @property string $clientId Идентификатор приложения
 * @property string $client_id Идентификатор приложения
 * @property string $clientName Название приложения (только для type = RequestorThirdPartyService)
 * @property string $client_name Название приложения (только для type = RequestorThirdPartyService)
 */
class Requestor extends AbstractObject implements RequestorInterface
{
    /**
     * @var string Тип инициатора
     */
    private $_type;

    /**
     * @var string Идентификатор магазина
     */
    private $_accountId;

    /**
     * @var string Идентификатор приложения
     */
    private $_clientId;

    /**
     * @var string Название приложения (только для type = RequestorThirdPartyService)
     */
    private $_clientName;

    /**
     * @inheritdoc
     * @param string $value Тип инициатора
     */
    public function setType($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } else {
            $this->_type = (string)$value;
        }
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @inheritdoc
     * @param string $value Идентификатор магазина
     */
    public function setAccountId($value)
    {
        if ($value === null || $value === '') {
            $this->_accountId = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } else {
            $this->_accountId = (string)$value;
        }
    }

    /**
     * @inheritdoc
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * @inheritdoc
     * @param string $value Идентификатор приложения
     */
    public function setClientId($value)
    {
        if ($value === null || $value === '') {
            $this->_clientId = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } else {
            $this->_clientId = (string)$value;
        }
    }

    /**
     * @inheritdoc
     * @return string|null
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @inheritdoc
     * @param string $value Название приложения
     */
    public function setClientName($value)
    {
        if ($value === null || $value === '') {
            $this->_clientName = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "accountId" parameter in Source', 0, 'source.accountId'
            );
        } else {
            $this->_clientName = (string)$value;
        }
    }

    /**
     * @inheritdoc
     * @return string|null
     */
    public function getClientName()
    {
        return $this->_clientName;
    }
}