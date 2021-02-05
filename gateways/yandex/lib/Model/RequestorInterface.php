<?php


namespace YooKassa\Model;


interface RequestorInterface
{
    /**
     * Возвращает тип инициатора
     * @return string
     */
    public function getType();

    /**
     * @return string|null
     */
    public function getAccountId();


    /**
     * @return string|null
     */
    public function getClientId();

    /**
     * @return string|null
     */
    public function getClientName();
}