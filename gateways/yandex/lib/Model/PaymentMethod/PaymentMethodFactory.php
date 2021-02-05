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

namespace YooKassa\Model\PaymentMethod;

use YooKassa\Model\PaymentMethodType;

class PaymentMethodFactory
{
    private $typeClassMap = array(
        PaymentMethodType::YOO_MONEY      => 'PaymentMethodYooMoney',
        PaymentMethodType::BANK_CARD      => 'PaymentMethodBankCard',
        PaymentMethodType::SBERBANK       => 'PaymentMethodSberbank',
        PaymentMethodType::CASH           => 'PaymentMethodCash',
        PaymentMethodType::MOBILE_BALANCE => 'PaymentMethodMobileBalance',
        PaymentMethodType::APPLE_PAY      => 'PaymentMethodApplePay',
        PaymentMethodType::GOOGLE_PAY     => 'PaymentMethodGooglePay',
        PaymentMethodType::QIWI           => 'PaymentMethodQiwi',
        PaymentMethodType::WEBMONEY       => 'PaymentMethodWebmoney',
        PaymentMethodType::ALFABANK       => 'PaymentMethodAlfaBank',
        PaymentMethodType::INSTALLMENTS   => 'PaymentMethodInstallments',
        PaymentMethodType::B2B_SBERBANK   => 'PaymentMethodB2bSberbank',
        PaymentMethodType::TINKOFF_BANK   => 'PaymentMethodTinkoffBank',
        PaymentMethodType::PSB            => 'PaymentMethodPsb',
        PaymentMethodType::WECHAT         => 'PaymentMethodWechat',
    );

    private $optionsMap = array(
        'card_type'      => 'cardType',
        'expiry_month'   => 'expiryMonth',
        'expiry_year'    => 'expiryYear',
        'account_number' => 'accountNumber',
    );

    /**
     * @param string $type
     *
     * @return AbstractPaymentMethod
     */
    public function factory($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Invalid payment method type value in payment factory');
        }
        if (!array_key_exists($type, $this->typeClassMap)) {
            throw new \InvalidArgumentException('Invalid payment method data type "'.$type.'"');
        }
        $className = __NAMESPACE__.'\\'.$this->typeClassMap[$type];

        return new $className();
    }

    /**
     * @param array $data
     * @param string|null $type
     *
     * @return AbstractPaymentMethod
     */
    public function factoryFromArray(array $data, $type = null)
    {
        if ($type === null) {
            if (array_key_exists('type', $data)) {
                $type = $data['type'];
                unset($data['type']);
            } else {
                throw new \InvalidArgumentException(
                    'Parameter type not specified in PaymentDataFactory.factoryFromArray()'
                );
            }
        }

        $paymentData = $this->factory($type);
        $this->fillModel($paymentData, $data);

        return $paymentData;
    }

    private function fillModel(AbstractPaymentMethod $paymentData, array $data)
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->optionsMap)) {
                $key = $this->optionsMap[$key];
            }
            if ($paymentData->offsetExists($key)) {
                $paymentData->offsetSet($key, $value);
            } else if (is_array($value)) {
                $this->fillModel($paymentData, $value);
            }
        }
    }
}