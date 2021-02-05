<?php


namespace YooKassa\Model;


use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

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
     * Requestor constructor.
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        $this->fromArray($data);
    }

    /**
     * @param $value
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
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param $value
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
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * @param string $value
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
     * @return string|null
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @param string $value
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
     * @return string|null
     */
    public function getClientName()
    {
        return $this->_clientName;
    }
}