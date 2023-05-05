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

namespace YooKassa\Model\PaymentMethod;

use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Model\PaymentData\B2b\Sberbank\VatData;
use YooKassa\Model\PaymentMethod\B2b\Sberbank\PayerBankDetails;
use YooKassa\Model\PaymentMethodType;

/**
 * Класс, описывающий метод оплаты, при оплате через Сбербанк Бизнес Онлайн
 *
 * @property string $paymentPurpose Назначение платежа
 * @property string $payment_purpose Назначение платежа
 * @property VatData $vatData Данные об НДС
 * @property PayerBankDetails $payerBankDetails Банковские реквизиты плательщика
 * @property PayerBankDetails $payer_bank_details Банковские реквизиты плательщика
 */
class PaymentMethodB2bSberbank extends AbstractPaymentMethod
{
    /**
     * @var string Назначение платежа
     */
    private $_paymentPurpose;

    /**
     * @var VatData Данные об НДС
     */
    private $_vatData;

    /**
     * @var PayerBankDetails Банковские реквизиты плательщика (юридического лица или ИП).
     */
    private $_payerBankDetails;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::B2B_SBERBANK);
    }

    /**
     * Возвращает назначение платежа
     * @return string Назначение платежа
     */
    public function getPaymentPurpose()
    {
        return $this->_paymentPurpose;
    }

    /**
     * Устанавливает назначение платежа
     * @param string $paymentPurpose Назначение платежа
     */
    public function setPaymentPurpose($paymentPurpose)
    {
        $this->_paymentPurpose = $paymentPurpose;
    }

    /**
     * Возвращает назначение платежа
     * @return VatData Данные об НДС
     */
    public function getVatData()
    {
        return $this->_vatData;
    }

    /**
     * Устанавливает назначение платежа
     * @param VatData $vatData Данные об НДС
     */
    public function setVatData($vatData)
    {
        if(is_array($vatData)) {
            $value = new VatData();
            $value->fromArray($vatData);
            $this->_vatData = $value;
        } else if($vatData instanceof VatData){
            $this->_vatData = $vatData;
        } else{
            throw new InvalidPropertyValueException('Invalid $vatData property type');
        }

    }

    /**
     * Возвращает банковские реквизиты плательщика (юридического лица или ИП)
     * @return PayerBankDetails Банковские реквизиты плательщика
     */
    public function getPayerBankDetails()
    {
        return $this->_payerBankDetails;
    }

    /**
     * Устанавливает Банковские реквизиты плательщика (юридического лица или ИП).
     * @param PayerBankDetails|array $payerBankDetails Банковские реквизиты плательщика
     */
    public function setPayerBankDetails($payerBankDetails)
    {
        if(is_array($payerBankDetails)) {
            $value = new PayerBankDetails();
            $value->fromArray($payerBankDetails);
            $this->_payerBankDetails = $value;
        } else if($payerBankDetails instanceof PayerBankDetails){
            $this->_payerBankDetails = $payerBankDetails;
        } else{
            throw new InvalidPropertyValueException('Invalid $payerBankDetails property type');
        }
    }
}
