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

namespace YooKassa\Request\Payments;

use InvalidArgumentException;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\AuthorizationDetails;
use YooKassa\Model\CancellationDetails;
use YooKassa\Model\Confirmation\ConfirmationCodeVerification;
use YooKassa\Model\Confirmation\ConfirmationEmbedded;
use YooKassa\Model\Confirmation\ConfirmationMobileApplication;
use YooKassa\Model\Confirmation\ConfirmationQr;
use YooKassa\Model\Confirmation\ConfirmationRedirect;
use YooKassa\Model\Confirmation\ConfirmationExternal;
use YooKassa\Model\ConfirmationType;
use YooKassa\Model\Metadata;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\Payment;
use YooKassa\Model\PaymentInterface;
use YooKassa\Model\PaymentMethod\AbstractPaymentMethod;
use YooKassa\Model\PaymentMethod\PaymentMethodFactory;
use YooKassa\Model\Recipient;
use YooKassa\Model\Transfer;

/**
 * Абстрактный класс ответа от API, возвращающего информацию о платеже
 *
 * @package YooKassa
 */
abstract class AbstractPaymentResponse extends Payment implements PaymentInterface
{
    /**
     * Конструктор, устанавливает настройки платежа из ассоциативного массива
     *
     * @param array $sourceArray Массив с информацией о платеже, пришедший от API
     * @throws \Exception
     */
    public function fromArray($sourceArray)
    {
        $this->setId($sourceArray['id']);
        $this->setStatus($sourceArray['status']);
        $this->setAmount($this->factoryAmount($sourceArray['amount']));
        $this->setCreatedAt($sourceArray['created_at']);
        $this->setPaid($sourceArray['paid']);
        $this->setRefundable($sourceArray['refundable']);
        if (!empty($sourceArray['test'])) {
            $this->setTest($sourceArray['test']);
        }
        if (!empty($sourceArray['payment_method'])) {
            $this->setPaymentMethod($this->factoryPaymentMethod($sourceArray['payment_method']));
        }

        if (!empty($sourceArray['description'])) {
            $this->setDescription($sourceArray['description']);
        }

        if (!empty($sourceArray['recipient'])) {
            $recipient = new Recipient();
            if (!empty($sourceArray['recipient']['account_id'])) {
                $recipient->setAccountId($sourceArray['recipient']['account_id']);
            }
            if (!empty($sourceArray['recipient']['gateway_id'])) {
                $recipient->setGatewayId($sourceArray['recipient']['gateway_id']);
            }
            $this->setRecipient($recipient);
        }
        if (!empty($sourceArray['captured_at'])) {
            $this->setCapturedAt($sourceArray['captured_at']);
        }
        if (!empty($sourceArray['expires_at'])) {
            $this->setExpiresAt($sourceArray['expires_at']);
        }
        if (!empty($sourceArray['confirmation'])) {
            $confirmationType = null;
            if (!empty($sourceArray['confirmation']['type'])) {
                $confirmationType = $sourceArray['confirmation']['type'];
            }

            switch ($confirmationType) {
                case ConfirmationType::REDIRECT:
                    $confirmation = new ConfirmationRedirect();
                    if (!empty($sourceArray['confirmation']['confirmation_url'])) {
                        $confirmation->setConfirmationUrl($sourceArray['confirmation']['confirmation_url']);
                    }
                    if (empty($sourceArray['confirmation']['enforce'])) {
                        $confirmation->setEnforce(false);
                    } else {
                        $confirmation->setEnforce($sourceArray['confirmation']['enforce']);
                    }
                    if (!empty($sourceArray['confirmation']['return_url'])) {
                        $confirmation->setReturnUrl($sourceArray['confirmation']['return_url']);
                    }
                    break;

                case ConfirmationType::EMBEDDED:
                    $confirmation = new ConfirmationEmbedded();
                    if (!empty($sourceArray['confirmation']['confirmation_token'])) {
                        $confirmation->setConfirmationToken($sourceArray['confirmation']['confirmation_token']);
                    }
                    break;

                case ConfirmationType::EXTERNAL:
                    $confirmation = new ConfirmationExternal();
                    break;

                case ConfirmationType::CODE_VERIFICATION:
                    $confirmation = new ConfirmationCodeVerification();
                    break;

                case ConfirmationType::QR:
                    $confirmation = new ConfirmationQr();
                    if (!empty($sourceArray['confirmation']['confirmation_data'])) {
                        $confirmation->setConfirmationData($sourceArray['confirmation']['confirmation_data']);
                    }
                    break;
                case ConfirmationType::MOBILE_APPLICATION:
                    $confirmation = new ConfirmationMobileApplication();
                    if (!empty($sourceArray['confirmation']['confirmation_url'])) {
                        $confirmation->setConfirmationUrl($sourceArray['confirmation']['confirmation_url']);
                    }
                    if (!empty($sourceArray['confirmation']['return_url'])) {
                        $confirmation->setReturnUrl($sourceArray['confirmation']['return_url']);
                    }
                    break;

            }

            if (isset($confirmation)) {
                $this->setConfirmation($confirmation);
            } else {
                throw new InvalidArgumentException('confirmation type '.$confirmationType.' is incorrect');
            }
        }
        if (!empty($sourceArray['refunded_amount'])) {
            $this->setRefundedAmount($this->factoryAmount($sourceArray['refunded_amount']));
        }
        if (!empty($sourceArray['receipt_registration'])) {
            $this->setReceiptRegistration($sourceArray['receipt_registration']);
        }
        if (!empty($sourceArray['metadata'])) {
            $metadata = new Metadata();
            foreach ($sourceArray['metadata'] as $key => $value) {
                $metadata->offsetSet($key, $value);
            }
            $this->setMetadata($metadata);
        }
        if (!empty($sourceArray['cancellation_details'])) {
            $cancellationDetails = $sourceArray['cancellation_details'];
            $party               = isset($cancellationDetails['party']) ? $cancellationDetails['party'] : null;
            $reason              = isset($cancellationDetails['reason']) ? $cancellationDetails['reason'] : null;
            $this->setCancellationDetails(new CancellationDetails($party, $reason));
        }
        if (!empty($sourceArray['authorization_details'])) {
            $this->setAuthorizationDetails(new AuthorizationDetails($sourceArray['authorization_details']));
        }
        if (!empty($sourceArray['transfers'])) {
            $transfers = array();
            foreach ($sourceArray['transfers'] as $transferDefinition) {
                $transfers[] = new Transfer($transferDefinition);
            }

            $this->setTransfers($transfers);
        }
        if (!empty($sourceArray['income_amount'])) {
            $this->setIncomeAmount($this->factoryAmount($sourceArray['income_amount']));
        }
        if (!empty($sourceArray['deal'])) {
            $this->setDeal($sourceArray['deal']);
        }
        if (!empty($sourceArray['merchant_customer_id'])) {
            $this->setMerchantCustomerId($sourceArray['merchant_customer_id']);
        }
    }

    /**
     * Фабричный метод для создания способа оплаты
     *
     * @param array $options Настройки способа оплаты в массиве
     *
     * @return AbstractPaymentMethod Инстанс способа оплаты нужного типа
     */
    private function factoryPaymentMethod($options)
    {
        $factory = new PaymentMethodFactory();

        return $factory->factoryFromArray($options);
    }

    /**
     * Фабричный метод создания суммы
     *
     * @param array $options Сумма в виде ассоциативного массива
     *
     * @return AmountInterface Созданный инстанс суммы
     */
    private function factoryAmount($options)
    {
        $amount = new MonetaryAmount(null, $options['currency']);
        if ($options['value'] > 0) {
            $amount->setValue($options['value']);
        }

        return $amount;
    }
}
