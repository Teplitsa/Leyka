<?php

namespace MixplatClient;

class MixplatVars
{
    /* Валюта платежа (currency) */
    const CURRENCY_RUB = 'RUB';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_BYN = 'BYN';
    const CURRENCY_KZT = 'KZT';
    const CURRENCY_UAH = 'UAH';

    /* Язык платежа (language) */
    const LANGUAGE_RU = 'RU';
    const LANGUAGE_EN = 'EN';

    /* Результат выполнения запроса */
    const RESULT_OK = 'ok';
    const RESULT_ERROR_INVALID_REQUEST = 'error_invalid_request';
    const RESULT_ERROR_SIGNATURE = 'error_signature';
    const RESULT_ERROR_NOT_FOUND = 'error_not_found';
    const RESULT_ERROR_PROJECT_NOT_FOUND = 'error_project_not_found';
    const RESULT_ERROR_PROJECT_NOT_ACTIVE = 'error_project_not_active';
    const RESULT_ERROR_METHOD_NOT_ACTIVE = 'error_method_not_active';
    const RESULT_ERROR_PAYMENT_PARAMENTERS = 'error_payment_paramenters';
    const RESULT_ERROR_FRAUD_CONTROL = 'error_fraud_control';
    const RESULT_ERROR_SETTINGS = 'error_settings';
    const RESULT_ERROR_UNABLE_TO_REFUND_YET = 'error_unable_to_refund_yet';
    const RESULT_ERROR_REFUND_NOT_AVAILABLE = 'error_refund_not_available';
    const RESULT_ERROR_REFUND_AMOUNT_EXCEED = 'error_refund_amount_exceed';
    const RESULT_ERROR_REFUND_NOT_FOUND = 'error_refund_not_found';
    const RESULT_ERROR_OTHER = 'error_other';
    const RESULT_ERROR_INTERNAL = 'error_internal';

    /* Статус платежа (status) */
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_SUCCESS = 'success';
    const PAYMENT_STATUS_FAILURE = 'failure';

    /* Расширенный статус платежа (extended_status) */
    const PAYMENT_EXT_STATUS_PENDING_DRAFT = 'pending_draft';
    const PAYMENT_EXT_STATUS_PENDING_QUEUED = 'pending_queued';
    const PAYMENT_EXT_STATUS_PENDING_PROCESSING = 'pending_processing';
    const PAYMENT_EXT_STATUS_PENDING_CHECK = 'pending_check';
    const PAYMENT_EXT_STATUS_PENDING_AUTHORIZED = 'pending_authorized';
    const PAYMENT_EXT_STATUS_SUCCESS_SUCCESS = 'success_success';
    const PAYMENT_EXT_STATUS_FAILURE_NOT_ENOUGH_MONEY = 'failure_not_enough_money';
    const PAYMENT_EXT_STATUS_FAILURE_GATE_ERROR = 'failure_gate_error';
    const PAYMENT_EXT_STATUS_FAILURE_CANCELED_BY_USER = 'failure_canceled_by_user';
    const PAYMENT_EXT_STATUS_FAILURE_CANCELED_BY_MERCHANT = 'failure_canceled_by_merchant';
    const PAYMENT_EXT_STATUS_FAILURE_PREVIOUS_PAYMENT = 'failure_previous_payment';
    const PAYMENT_EXT_STATUS_FAILURE_NOT_AVAILABLE = 'failure_not_available';
    const PAYMENT_EXT_STATUS_FAILURE_ACCEPT_TIMEOUT = 'failure_accept_timeout';
    const PAYMENT_EXT_STATUS_FAILURE_LIMITS = 'failure_limits';
    const PAYMENT_EXT_STATUS_FAILURE_OTHER = 'failure_other';
    const PAYMENT_EXT_STATUS_FAILURE_MIN_AMOUNT = 'failure_min_amount';
    const PAYMENT_EXT_STATUS_FAILURE_PENDING_TIMEOUT = 'failure_pending_timeout';

    /* Статус возврата (refund_status) */
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILURE = 'failure';

    /* Платёжный метод (payment_method) */
    const PAYMENT_METHOD_MOBILE = 'mobile';
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_WALLET = 'wallet';
    const PAYMENT_METHOD_BANK = 'bank';

    /* Платёжная система (payment_system) */
    /* для payment_method = mobile (оплата с телефона)	 */
    const PAYMENT_MOBILE_RU_MTS = 'mobile_ru_mts';
    const PAYMENT_SYSTEM_MOBILE_RU_BEELINE = 'mobile_ru_beeline';
    const PAYMENT_SYSTEM_MOBILE_RU_MEGAFON = 'mobile_ru_megafon';
    const PAYMENT_SYSTEM_MOBILE_RU_TELE2 = 'mobile_ru_tele2';
    const PAYMENT_SYSTEM_MOBILE_RU_TATTELECOM = 'mobile_ru_tattelecom';
    const PAYMENT_SYSTEM_MOBILE_RU_YOTA = 'mobile_ru_yota';
    const PAYMENT_SYSTEM_MOBILE_OTHER = 'mobile_other';
    /* для payment_method = card (банковские карты)	*/
    const PAYMENT_SYSTEM_VISA = 'visa';
    const PAYMENT_SYSTEM_MASTERCARD = 'mastercard';
    const PAYMENT_SYSTEM_MIR = 'mir';
    const PAYMENT_SYSTEM_UNIONPAY = 'unionpay';
    const PAYMENT_SYSTEM_JCB = 'jcb';
    const PAYMENT_SYSTEM_APPLE_PAY = 'apple_pay';
    const PAYMENT_SYSTEM_GOOGLE_PAY = 'google_pay';
    const PAYMENT_SYSTEM_OTHER = 'other';
    /* для payment_method = wallet (электронные кошельки) */
    const PAYMENT_SYSTEM_YANDEX_MONEY = 'yandex_money';
    const PAYMENT_SYSTEM_WEBMONEY = 'webmoney';
    const PAYMENT_SYSTEM_QIWI = 'qiwi';
    /* для payment_method = bank (банк-клиент) */
    const PAYMENT_SYSTEM_SBERBANK_ONLINE = 'sberbank_online';
    const PAYMENT_SYSTEM_ALFA_CLICK = 'alfa_click';

    /* Тип биллинга (billing_type) */
    /* для payment_method = mobile (оплата с телефона)	 */
    const BILLING_TYPE_MC = 'mc';
    const BILLING_TYPE_CPA = 'cpa';

    /* Схема проведения платежа по банковским картам */
    const CARD_SHEME_SMS = 'sms';
    const CARD_SHEME_DMS = 'dms';


    /* Фискализация */
    const ITEMS_VAT_NONE = 'none';
    const ITEMS_VAT_VAT0 = 'vat0';
    const ITEMS_VAT_VAT10 = 'vat10';
    const ITEMS_VAT_VAT110 = 'vat110';
    const ITEMS_VAT_VAT20 = 'vat20';
    const ITEMS_VAT_VAT120 = 'vat120';

    const ITEMS_METHOD_FULL_PREPAYMENT = 'full_prepayment';
    const ITEMS_METHOD_PREPAYMENT = 'prepayment';
    const ITEMS_METHOD_ADVANCE = 'advance';
    const ITEMS_METHOD_FULL_PAYMENT = 'full_payment';
    const ITEMS_METHOD_PARTIAL_PAYMENT = 'partial_payment';
    const ITEMS_METHOD_CREDIT = 'credit';
    const ITEMS_METHOD_CREDIT_PAYMENT = 'credit_payment';

    const ITEMS_OBJECT_COMMODITY = 'commodity';
    const ITEMS_OBJECT_EXCISE = 'excise';
    const ITEMS_OBJECT_JOB = 'job';
    const ITEMS_OBJECT_SERVICE = 'service';
    const ITEMS_OBJECT_PAYMENT = 'payment';
    const ITEMS_OBJECT_PROPERTY_RIGHT = 'property_right';
    const ITEMS_OBJECT_COMPOSITE = 'composite';
    const ITEMS_OBJECT_ANOTHER = 'another';


    const NOTIFY_REQUEST_PAYMENT_STATUS = 'payment_status';
    const NOTIFY_REQUEST_REFUND_STATUS = 'refund_status';
    const NOTIFY_REQUEST_PAYMENT_CHECK = 'payment_check';
    const NOTIFY_REQUEST_SMS = 'sms';
}
