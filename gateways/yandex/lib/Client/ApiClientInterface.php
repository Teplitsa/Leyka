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

namespace YooKassa\Client;

use Psr\Log\LoggerInterface;
use YooKassa\Common\ResponseObject;

/**
 * Interface ApiClientInterface
 *
 * @package YooKassa
 */
interface ApiClientInterface
{
    /**
     * Создает CURL запрос, получает и возвращает обработанный ответ
     *
     * @param string $path URL запроса
     * @param string $method HTTP метод
     * @param array $queryParams Массив GET параметров запроса
     * @param string|null $httpBody Тело запроса
     * @param array $headers Массив заголовков запроса
     *
     * @return ResponseObject
     */
    public function call($path, $method, $queryParams, $httpBody = null, $headers = array());

    /**
     * Устанавливает объект для логирования
     *
     * @param LoggerInterface|null $logger Объект для логирования
     */
    public function setLogger($logger);

    /**
     * Возвращает UserAgent
     *
     * @return UserAgent
     */
    public function getUserAgent();

    /**
     * Устанавливает shopId магазина
     *
     * @param string|int $shopId shopId магазина
     * @return mixed
     */
    public function setShopId($shopId);

    /**
     * Устанавливает секретный ключ магазина
     *
     * @param string $shopPassword
     * @return mixed
     */
    public function setShopPassword($shopPassword);

    /**
     * Устанавливает OAuth-токен магазина
     *
     * @param string $bearerToken
     * @return mixed
     */
    public function setBearerToken($bearerToken);

    /**
     * Устанавливает настройки
     *
     * @param array $config
     */
    public function setConfig($config);
}
