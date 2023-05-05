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

namespace YooKassa\Model\Confirmation;


use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\ConfirmationType;

/**
 * @property string $confirmationData URL для создания QR-кода
 * @property string $confirmation_data URL для создания QR-кода
 */
class ConfirmationQr extends AbstractConfirmation
{
    private $_confirmationData;

    public function __construct()
    {
        $this->setType(ConfirmationType::QR);
    }

    /**
     * @return string
     */
    public function getConfirmationData()
    {
        return $this->_confirmationData;
    }

    /**
     * @param string $confirmationData
     */
    public function setConfirmationData($confirmationData)
    {
        if ($confirmationData === null || $confirmationData === '') {
            $this->_confirmationData = null;
        } elseif (TypeCast::canCastToString($confirmationData)) {
            $this->_confirmationData = (string)$confirmationData;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid confirmationData value type', 0, 'confirmationQr.confirmationData', $confirmationData
            );
        }
    }
}
