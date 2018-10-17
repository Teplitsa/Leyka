<?php

/**
 * The MIT License
 *
 * Copyright (c) 2017 NBCO Yandex.Money LLC
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

namespace YandexCheckout\Request\Payments;

use YandexCheckout\Model\AmountInterface;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\LegInterface;
use YandexCheckout\Model\PassengerInterface;
use YandexCheckout\Model\PaymentData\AbstractPaymentData;
use YandexCheckout\Model\PaymentData\PaymentDataAlfabank;
use YandexCheckout\Model\PaymentData\PaymentDataBankCard;
use YandexCheckout\Model\PaymentData\PaymentDataGooglePay;
use YandexCheckout\Model\PaymentData\PaymentDataSberbank;
use YandexCheckout\Model\PaymentData\PaymentDataYandexWallet;
use YandexCheckout\Model\PaymentMethodType;

/**
 * Класс сериалайзера объекта запроса к API на проведение платежа
 *
 * @package YandexCheckout\Request\Payments
 */
class CreatePaymentRequestSerializer
{
    private static $propertyMap = array(
        'reference_id'      => 'referenceId',
        'payment_token'     => 'paymentToken',
        'payment_method_id' => 'paymentMethodId',
        'client_ip'         => 'clientIp',
    );

    private static $paymentDataSerializerMap = array(
        PaymentMethodType::BANK_CARD      => 'serializePaymentDataBankCard',
        PaymentMethodType::YANDEX_MONEY   => 'serializePaymentDataYandexWallet',
        PaymentMethodType::APPLE_PAY      => 'serializePaymentDataMobile',
        PaymentMethodType::GOOGLE_PAY     => 'serializePaymentDataGooglePay',
        PaymentMethodType::SBERBANK       => 'serializePaymentDataSberbank',
        PaymentMethodType::ALFABANK       => 'serializePaymentDataAlfabank',
        PaymentMethodType::WEBMONEY       => 'serializePaymentData',
        PaymentMethodType::QIWI           => 'serializePaymentDataMobilePhone',
        PaymentMethodType::CASH           => 'serializePaymentDataMobilePhone',
        PaymentMethodType::MOBILE_BALANCE => 'serializePaymentDataMobilePhone',
        PaymentMethodType::INSTALLMENTS   => 'serializePaymentData',
    );

    public function serialize(CreatePaymentRequestInterface $request)
    {
        $result = array(
            'amount' => $this->serializeAmount($request->getAmount()),
        );
        if ($request->hasDescription()) {
            $result['description'] = $request->getDescription();
        }
        if ($request->hasReceipt()) {
            $receipt = $request->getReceipt();
            if ($receipt->notEmpty()) {
                $result['receipt'] = array();
                foreach ($receipt->getItems() as $item) {
                    $result['receipt']['items'][] = array(
                        'description' => $item->getDescription(),
                        'amount'      => $this->serializeAmount($item->getPrice()),
                        'quantity'    => $item->getQuantity(),
                        'vat_code'    => $item->getVatCode(),
                    );
                }
                $value = $receipt->getEmail();
                if (!empty($value)) {
                    $result['receipt']['email'] = $value;
                }
                $value = $receipt->getPhone();
                if (!empty($value)) {
                    $result['receipt']['phone'] = $value;
                }
                $value = $receipt->getTaxSystemCode();
                if (!empty($value)) {
                    $result['receipt']['tax_system_code'] = $value;
                }
            }
        }
        if ($request->hasRecipient()) {
            $result['recipient']['account_id'] = $request->getRecipient()->getAccountId();
            $result['recipient']['gateway_id'] = $request->getRecipient()->getGatewayId();
        }
        if ($request->hasPaymentMethodData()) {
            $method                        = self::$paymentDataSerializerMap[$request->getPaymentMethodData()->getType()];
            $result['payment_method_data'] = $this->{$method}($request->getPaymentMethodData());
        }
        if ($request->hasConfirmation()) {
            $result['confirmation'] = array(
                'type' => $request->getConfirmation()->getType(),
            );
            $confirmation           = $request->getConfirmation();
            if ($confirmation->getType() === ConfirmationType::REDIRECT) {
                if ($confirmation->getEnforce()) {
                    $result['confirmation']['enforce'] = $confirmation->getEnforce();
                }
                $result['confirmation']['return_url'] = $confirmation->getReturnUrl();
            }
        }
        if ($request->hasMetadata()) {
            $result['metadata'] = $request->getMetadata()->toArray();
        }
        if ($request->hasCapture()) {
            $result['capture'] = $request->getCapture();
        }
        if ($request->hasSavePaymentMethod()) {
            $result['save_payment_method'] = $request->getSavePaymentMethod();
        }
        if ($request->hasAirline()) {
            $airline           = $request->getAirline();
            $result['airline'] = array();

            $ticketNumber = $airline->getTicketNumber();
            if (!empty($ticketNumber)) {
                $result['airline']['ticket_number'] = $ticketNumber;
            }
            $bookingReference = $airline->getBookingReference();
            if (!empty($bookingReference)) {
                $result['airline']['booking_reference'] = $bookingReference;
            }

            /** @var PassengerInterface $passenger */
            foreach ($airline->getPassengers() as $passenger) {
                $result['airline']['passengers'][] = array(
                    'first_name' => $passenger->getFirstName(),
                    'last_name'  => $passenger->getLastName(),
                );
            }

            /** @var LegInterface $leg */
            foreach ($airline->getLegs() as $leg) {
                $result['airline']['legs'][] = array(
                    'departure_airport'   => $leg->getDepartureAirport(),
                    'destination_airport' => $leg->getDestinationAirport(),
                    'departure_date'      => $leg->getDepartureDate(),
                );
            }
        }

        foreach (self::$propertyMap as $name => $property) {
            $value = $request->{$property};
            if (!empty($value)) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    private function serializeAmount(AmountInterface $amount)
    {
        return array(
            'value'    => $amount->getValue(),
            'currency' => $amount->getCurrency(),
        );
    }

    private function serializePaymentDataBankCard(PaymentDataBankCard $paymentData)
    {
        $result = array(
            'type' => $paymentData->getType(),
        );
        if ($paymentData->getCard() !== null) {
            $result['card'] = array(
                'cardholder'   => $paymentData->getCard()->getCardholder(),
                'expiry_year'  => $paymentData->getCard()->getExpiryYear(),
                'expiry_month' => $paymentData->getCard()->getExpiryMonth(),
                'number'       => $paymentData->getCard()->getNumber(),
                'csc'          => $paymentData->getCard()->getCsc(),
            );
        }

        return $result;
    }

    private function serializePaymentDataYandexWallet(PaymentDataYandexWallet $paymentData)
    {
        $result = array(
            'type' => $paymentData->getType(),
        );

        return $result;
    }

    private function serializePaymentDataMobile(AbstractPaymentData $paymentData)
    {
        $result = array(
            'type' => $paymentData->getType(),
        );
        if ($paymentData->getPaymentData() !== null) {
            $result['payment_data'] = $paymentData->getPaymentData();
        }

        return $result;
    }

    private function serializePaymentDataSberbank(PaymentDataSberbank $paymentData)
    {
        $result = array(
            'type' => $paymentData->getType(),
        );
        if ($paymentData->getPhone() !== null) {
            $result['phone'] = $paymentData->getPhone();
        }

        return $result;
    }

    private function serializePaymentDataAlfabank(PaymentDataAlfabank $paymentData)
    {
        $result = array(
            'type' => $paymentData->getType(),
        );
        if ($paymentData->getLogin() !== null) {
            $result['login'] = $paymentData->getLogin();
        }

        return $result;
    }

    private function serializePaymentData(AbstractPaymentData $paymentData)
    {
        return array(
            'type' => $paymentData->getType(),
        );
    }

    private function serializePaymentDataMobilePhone(AbstractPaymentData $paymentData)
    {
        $result = array(
            'type' => $paymentData->getType(),
        );
        if ($paymentData->getPhone() !== null) {
            $result['phone'] = $paymentData->getPhone();
        }

        return $result;
    }

    /**
     * @param PaymentDataGooglePay $paymentData
     * @return array
     */
    private function serializePaymentDataGooglePay(PaymentDataGooglePay $paymentData)
    {
        $result = array(
            'type'                  => $paymentData->getType(),
            'payment_method_token'  => $paymentData->getPaymentMethodToken(),
            'google_transaction_id' => $paymentData->getGoogleTransactionId(),
        );

        return $result;
    }
}