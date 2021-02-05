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

namespace YooKassa\Model\Receipt;


use YooKassa\Common\AbstractEnum;

class PaymentMode extends AbstractEnum
{
    const FULL_PREPAYMENT = 'full_prepayment';
    const PARTIAL_PREPAYMENT = 'partial_prepayment';
    const ADVANCE = 'advance';
    const FULL_PAYMENT = 'full_payment';
    const PARTIAL_PAYMENT = 'partial_payment';
    const CREDIT = 'credit';
    const CREDIT_PAYMENT = 'credit_payment';

    protected static $validValues = array(
        self::FULL_PREPAYMENT    => true,
        self::PARTIAL_PREPAYMENT => true,
        self::ADVANCE            => true,
        self::FULL_PAYMENT       => true,
        self::PARTIAL_PAYMENT    => true,
        self::CREDIT             => true,
        self::CREDIT_PAYMENT     => true,
    );
}