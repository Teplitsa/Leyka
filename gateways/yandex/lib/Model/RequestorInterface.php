<?php


namespace YooKassa\Model;

/**
 * Interface RequestorInterface
 *
 * Инициатор платежа или возврата
 *
 * Инициатором может быть магазин, подключенный к ЮKassa, `merchant` или приложение, которому владелец магазина
 * [разрешил](https://yookassa.ru/developers/partners-api/basics) совершать операции от своего имени `third_party_client`.
 *
 * @package YooKassa
 * @deprecated Не используется. Будет удален в следующих версиях
 */
interface RequestorInterface
{
    /**
     * Возвращает тип инициатора
     * @return string
     */
    public function getType();

    /**
     * Устанавливает тип инициатора
     * @param string $value Тип инициатора
     */
    public function setType($value);

    /**
     * Возвращает идентификатор магазина
     * @return string|null
     */
    public function getAccountId();

    /**
     * Устанавливает идентификатор магазина
     * @param string $value Идентификатор магазина
     */
    public function setAccountId($value);

    /**
     * Возвращает идентификатор приложения
     * @return string|null
     */
    public function getClientId();

    /**
     * Устанавливает идентификатор приложения
     * @param string $value Идентификатор приложения
     */
    public function setClientId($value);

    /**
     * Возвращает название приложения
     * @return string|null
     */
    public function getClientName();

    /**
     * Устанавливает название приложения
     * @param string $value Название приложения
     */
    public function setClientName($value);
}