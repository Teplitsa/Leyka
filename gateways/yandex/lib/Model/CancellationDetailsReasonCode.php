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

use YooKassa\Common\AbstractEnum;

/**
 * CancellationDetailsReasonCode - Возможные причины отмены платежа
 */
class CancellationDetailsReasonCode extends AbstractEnum
{
    const THREE_D_SECURE_FAILED = '3d_secure_failed';
    const CALL_ISSUER = 'call_issuer';
    const CARD_EXPIRED = 'card_expired';
    const COUNTRY_FORBIDDEN = 'country_forbidden';
    const FRAUD_SUSPECTED = 'fraud_suspected';
    const GENERAL_DECLINE = 'general_decline';
    const IDENTIFICATION_REQUIRED = 'identification_required';
    const INSUFFICIENT_FUNDS = 'insufficient_funds';
    const INVALID_CARD_NUMBER = 'invalid_card_number';
    const INVALID_CSC = 'invalid_csc';
    const ISSUER_UNAVAILABLE = 'issuer_unavailable';
    const PAYMENT_METHOD_LIMIT_EXCEEDED = 'payment_method_limit_exceeded';
    const PAYMENT_METHOD_RESTRICTED = 'payment_method_restricted';
    const PERMISSION_REVOKED = 'permission_revoked';
    const INTERNAL_TIMEOUT = 'internal_timeout';
    const CANCELED_BY_MERCHANT = 'canceled_by_merchant';
    const PAYMENT_EXPIRED = 'payment_expired';
    const EXPIRED_ON_CONFIRMATION = 'expired_on_confirmation';
    const EXPIRED_ON_CAPTURE = 'expired_on_capture';

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
        self::PAYMENT_EXPIRED               => true,
        self::EXPIRED_ON_CONFIRMATION       => true,
        self::EXPIRED_ON_CAPTURE            => true,
    );
}
