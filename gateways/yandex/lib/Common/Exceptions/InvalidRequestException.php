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

namespace YandexCheckout\Common\Exceptions;

use YandexCheckout\Common\AbstractRequest;

class InvalidRequestException extends \RuntimeException
{
    /**
     * @var AbstractRequest|null
     */
    private $errorRequest;

    /**
     * InvalidRequestException constructor.
     * @param AbstractRequest|string $error
     * @param int $code
     * @param null $previous
     */
    public function __construct($error, $code = 0, $previous = null)
    {
        if ($error instanceof AbstractRequest) {
            $message = 'Failed to build request "'.get_class($error).'": "'.$error->getLastValidationError().'"';
            $this->errorRequest = $error;
        } else {
            $message = $error;
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return AbstractRequest|null
     */
    public function getRequestObject()
    {
        return $this->errorRequest;
    }
}