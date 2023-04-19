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

namespace YooKassa\Request\Payments\Payment;

use YooKassa\Common\AbstractPaymentRequestBuilder;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\InvalidPropertyException;
use YooKassa\Common\Exceptions\InvalidRequestException;

class CreateCaptureRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * Собираемый объект запроса
     * @var CreateCaptureRequest
     */
    protected $currentObject;

    /**
     * @return CreateCaptureRequest
     */
    protected function initCurrentObject()
    {
        parent::initCurrentObject();

        return new CreateCaptureRequest();
    }

    /**
     * Осуществляет сборку объекта запроса к API
     * @param array|null $options Массив дополнительных настроек объекта
     * @return CreateCaptureRequestInterface|AbstractRequest Иснатс объекта запроса к API
     *
     * @throws InvalidRequestException Выбрасывается если при валидации запроса произошла ошибка
     * @throws InvalidPropertyException Выбрасывается если не удалось установить один из параметров, переданных в массиве настроек
     */
    public function build(array $options = null)
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
        if (!empty($this->transfers)) {
            $this->currentObject->setTransfers($this->transfers);
        }
        if ($this->amount->getValue() > 0) {
            $this->currentObject->setAmount($this->amount);
        }
        if ($this->receipt->notEmpty()) {
            $this->currentObject->setReceipt($this->receipt);
        }
        return parent::build();
    }
}
