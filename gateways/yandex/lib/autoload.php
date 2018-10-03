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

define('YANDEX_CHECKOUT_SDK_ROOT_PATH', dirname(__FILE__));
define('YANDEX_CHECKOUT_PSR_LOG_PATH', dirname(__FILE__).'/../vendor/psr/log/Psr/Log');

function yandexCheckoutLoadClass($className)
{
    if (strncmp('YandexCheckout', $className, 14) === 0) {
        $path   = YANDEX_CHECKOUT_SDK_ROOT_PATH;
        $length = 14;
    } elseif (strncmp('Psr\Log', $className, 7) === 0) {
        $path   = YANDEX_CHECKOUT_PSR_LOG_PATH;
        $length = 7;
    } else {
        return;
    }
    $path .= str_replace('\\', '/', substr($className, $length)) . '.php';
    if (file_exists($path)) {
        require $path;
    }
}

spl_autoload_register('yandexCheckoutLoadClass');
