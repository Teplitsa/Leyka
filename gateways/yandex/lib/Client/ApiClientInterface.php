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

namespace YooKassa\Client;

use Psr\Log\LoggerInterface;

/**
 * Interface ApiClientInterface
 * @package YooKassa\Client
 */
interface ApiClientInterface
{
    /**
     * @param $path
     * @param $method
     * @param $queryParams
     * @param $httpBody
     * @param $headers
     * @return mixed
     */
    public function call($path, $method, $queryParams, $httpBody = null, $headers = array());

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger($logger);

    /**
     * @return UserAgent
     */
    public function getUserAgent();

    /**
     * @param $shopId
     * @return mixed
     */
    public function setShopId($shopId);

    /**
     * @param $shopPassword
     * @return mixed
     */
    public function setShopPassword($shopPassword);

    /**
     * @param $bearerToken
     * @return mixed
     */
    public function setBearerToken($bearerToken);

    /**
     * @param array $config
     */
    public function setConfig($config);
}