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

namespace YooKassa\Model;

use YooKassa\Common\AbstractEnum;

/**
 * CancellationDetailsReasonCode - Возможные причины отмены платежа
 */
class CancellationDetailsReasonCode extends AbstractEnum
{
    /**
     * Не пройдена аутентификация по 3-D Secure. При новой попытке оплаты пользователю следует пройти аутентификацию,
     * использовать другое платежное средство или обратиться в банк за уточнениями
     */
    const THREE_D_SECURE_FAILED = '3d_secure_failed';
    /**
     * Оплата данным платежным средством отклонена по неизвестным причинам.
     * Пользователю следует обратиться в организацию, выпустившую платежное средство
     */
    const CALL_ISSUER = 'call_issuer';
    /** Истек срок действия банковской карты. При новой попытке оплаты пользователю следует использовать другое платежное средство */
    const CARD_EXPIRED = 'card_expired';
    /**
     * Нельзя заплатить банковской картой, выпущенной в этой стране.
     * При новой попытке оплаты пользователю следует использовать другое платежное средство.
     * Вы можете настроить ограничения на оплату иностранными банковскими картами
     */
    const COUNTRY_FORBIDDEN = 'country_forbidden';
    /** Платеж заблокирован из-за подозрения в мошенничестве. При новой попытке оплаты пользователю следует использовать другое платежное средство */
    const FRAUD_SUSPECTED = 'fraud_suspected';
    /** Причина не детализирована. Пользователю следует обратиться к инициатору отмены платежа за уточнением подробностей */
    const GENERAL_DECLINE = 'general_decline';
    /** Превышены ограничения на платежи для кошелька ЮMoney. При новой попытке оплаты пользователю следует идентифицировать кошелек или выбрать другое платежное средство */
    const IDENTIFICATION_REQUIRED = 'identification_required';
    /** Не хватает денег для оплаты. Пользователю следует пополнить баланс или использовать другое платежное средство */
    const INSUFFICIENT_FUNDS = 'insufficient_funds';
    /** Неправильно указан номер карты. При новой попытке оплаты пользователю следует ввести корректные данные */
    const INVALID_CARD_NUMBER = 'invalid_card_number';
    /** Неправильно указан код CVV2 (CVC2, CID). При новой попытке оплаты пользователю следует ввести корректные данные */
    const INVALID_CSC = 'invalid_csc';
    /** Организация, выпустившая платежное средство, недоступна. При новой попытке оплаты пользователю следует использовать другое платежное средство или повторить оплату позже */
    const ISSUER_UNAVAILABLE = 'issuer_unavailable';
    /**
     * Исчерпан лимит платежей для данного платежного средства или вашего магазина.
     * При новой попытке оплаты пользователю следует использовать другое платежное средство или повторить оплату на следующий день
     */
    const PAYMENT_METHOD_LIMIT_EXCEEDED = 'payment_method_limit_exceeded';
    /** Запрещены операции данным платежным средством (например, карта заблокирована из-за утери, кошелек — из-за взлома мошенниками). Пользователю следует обратиться в организацию, выпустившую платежное средство */
    const PAYMENT_METHOD_RESTRICTED = 'payment_method_restricted';
    /** Нельзя провести безакцептное списание: пользователь отозвал разрешение на автоплатежи. Если пользователь еще хочет оплатить, вам необходимо создать новый платеж, а пользователю — подтвердить оплату */
    const PERMISSION_REVOKED = 'permission_revoked';
    /** Технические неполадки на стороне ЮKassa: не удалось обработать запрос в течение 30 секунд. Повторите платеж с новым ключом идемпотентности */
    const INTERNAL_TIMEOUT = 'internal_timeout';
    /** Платеж отменен по API при оплате в две стадии */
    const CANCELED_BY_MERCHANT = 'canceled_by_merchant';
    /**
     * Истек срок оплаты: пользователь не подтвердил платеж за время, отведенное на оплату выбранным способом.
     * Если пользователь еще хочет оплатить, вам необходимо повторить платеж с новым ключом идемпотентности, а пользователю — подтвердить его
     */
    const EXPIRED_ON_CONFIRMATION = 'expired_on_confirmation';
    /**
     * Истек срок списания оплаты у двухстадийного платежа.
     * Если вы еще хотите принять оплату, повторите платеж с новым ключом идемпотентности и спишите деньги после подтверждения платежа пользователем
     */
    const EXPIRED_ON_CAPTURE = 'expired_on_capture';
    /** Для тех, кто использует Безопасную сделку: закончился срок жизни сделки.
     * Если вы еще хотите принять оплату, создайте новую сделку и проведите для нее новый платеж
     */
    const DEAL_EXPIRED = 'deal_expired';
    /** Нельзя заплатить с номера телефона этого мобильного оператора. При новой попытке оплаты пользователю следует использовать другое платежное средство. Список поддерживаемых операторов */
    const UNSUPPORTED_MOBILE_OPERATOR = 'unsupported_mobile_operator';

    protected static $validValues = array(
        self::THREE_D_SECURE_FAILED         => true,
        self::CALL_ISSUER                   => true,
        self::CARD_EXPIRED                  => true,
        self::COUNTRY_FORBIDDEN             => true,
        self::FRAUD_SUSPECTED               => true,
        self::GENERAL_DECLINE               => true,
        self::IDENTIFICATION_REQUIRED       => true,
        self::INSUFFICIENT_FUNDS            => true,
        self::INVALID_CARD_NUMBER           => true,
        self::INVALID_CSC                   => true,
        self::ISSUER_UNAVAILABLE            => true,
        self::PAYMENT_METHOD_LIMIT_EXCEEDED => true,
        self::PAYMENT_METHOD_RESTRICTED     => true,
        self::PERMISSION_REVOKED            => true,
        self::INTERNAL_TIMEOUT              => true,
        self::CANCELED_BY_MERCHANT          => true,
        self::EXPIRED_ON_CONFIRMATION       => true,
        self::EXPIRED_ON_CAPTURE            => true,
        self::DEAL_EXPIRED                  => true,
        self::UNSUPPORTED_MOBILE_OPERATOR   => true,
    );
}
