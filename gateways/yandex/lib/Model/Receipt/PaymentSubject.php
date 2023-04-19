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

namespace YooKassa\Model\Receipt;


use YooKassa\Common\AbstractEnum;

class PaymentSubject extends AbstractEnum
{
    const COMMODITY = 'commodity';
    const EXCISE = 'excise';
    const JOB = 'job';
    const SERVICE = 'service';
    const GAMBLING_BET = 'gambling_bet';
    const GAMBLING_PRIZE = 'gambling_prize';
    const LOTTERY = 'lottery';
    const LOTTERY_PRIZE = 'lottery_prize';
    const INTELLECTUAL_ACTIVITY = 'intellectual_activity';
    const PAYMENT = 'payment';
    const AGENT_COMMISSION = 'agent_commission';
    const PROPERTY_RIGHT = 'property_right';
    const NON_OPERATING_GAIN = 'non_operating_gain';
    const INSURANCE_PREMIUM = 'insurance_premium';
    const SALES_TAX = 'sales_tax';
    const RESORT_FEE = 'resort_fee';
    const COMPOSITE = 'composite';
    const ANOTHER = 'another';

    protected static $validValues = array(
        self::COMMODITY             => true,
        self::EXCISE                => true,
        self::JOB                   => true,
        self::SERVICE               => true,
        self::GAMBLING_BET          => true,
        self::GAMBLING_PRIZE        => true,
        self::LOTTERY               => true,
        self::LOTTERY_PRIZE         => true,
        self::INTELLECTUAL_ACTIVITY => true,
        self::PAYMENT               => true,
        self::AGENT_COMMISSION      => true,
        self::PROPERTY_RIGHT        => true,
        self::NON_OPERATING_GAIN    => true,
        self::INSURANCE_PREMIUM     => true,
        self::SALES_TAX             => true,
        self::RESORT_FEE            => true,
        self::COMPOSITE             => true,
        self::ANOTHER               => true,
    );
}
