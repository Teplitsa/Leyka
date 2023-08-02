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

namespace YooKassa\Request\Receipts;

use YooKassa\Model\ReceiptItem;
use YooKassa\Model\ReceiptType;
use YooKassa\Model\Settlement;

/**
 * Класс сериалайзера объекта запроса к API создание чека
 *
 * @package YooKassa
 */
class CreatePostReceiptRequestSerializer
{
    /**
     * Сериализует объект запроса к API для дальнейшей его отправки
     *
     * @param CreatePostReceiptRequestInterface $request Сериализуемый объект
     * @return array Массив с информацией, отправляемый в дальнейшем в API
     */
    public function serialize(CreatePostReceiptRequestInterface $request)
    {
        $result = array_merge(array(
            'type' => $request->getType(),
            'send' => $request->getSend(),
        ), $this->serializeObjectId($request));

        /** @var Settlement $item */
        foreach ($request->getSettlements() as $item) {
            $result['settlements'][] = $item->jsonSerialize();
        }

        /** @var ReceiptItem $item */
        foreach ($request->getItems() as $item) {
            $result['items'][] = $item->jsonSerialize();
        }

        $customer = $request->getCustomer();
        if (!empty($customer)) {
            $result['customer'] = $customer->jsonSerialize();
        }

        $value = $request->getTaxSystemCode();
        if (!empty($value)) {
            $result['tax_system_code'] = $value;
        }

        $onBehalfOf = $request->getOnBehalfOf();
        if (!empty($onBehalfOf)) {
            $result['on_behalf_of'] = $onBehalfOf;
        }

        return $result;
    }

    /**
     * @param CreatePostReceiptRequestInterface $request
     * @return array
     */
    private function serializeObjectId(CreatePostReceiptRequestInterface $request)
    {
        $result = array();

        if ($request->getObjectType() === ReceiptType::PAYMENT) {
            $result['payment_id'] = $request->getObjectId();
        } elseif ($request->getObjectType() === ReceiptType::REFUND) {
            $result['refund_id'] = $request->getObjectId();
        }

        return $result;
    }
}
