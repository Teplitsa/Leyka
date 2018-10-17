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

namespace YandexCheckout\Helpers;

class RawHeadersParser
{
    public static function parse($rawHeaders)
    {
        $headers = array();
        $key = '';

        foreach (explode("\n", $rawHeaders) as $headerRow) {
            if (trim($headerRow) === '') {
                break;
            }
            $headerArray = explode(':', $headerRow, 2);

            if (isset($headerArray[1])) {
                if (!isset($headers[$headerArray[0]])) {
                    $headers[trim($headerArray[0])] = trim($headerArray[1]);
                } elseif (is_array($headers[$headerArray[0]])) {
                    $headers[trim($headerArray[0])] = array_merge($headers[trim($headerArray[0])], array(trim($headerArray[1])));
                } else {
                    $headers[trim($headerArray[0])] = array_merge(array($headers[trim($headerArray[0])]), array(trim($headerArray[1])));
                }

                $key = $headerArray[0];
            } else {
                if (substr($headerArray[0], 0, 1) === "\t") {
                    $headers[$key] .= "\r\n\t" . trim($headerArray[0]);
                } elseif (!$key) {
                    $headers[0] = trim($headerArray[0]);
                }
            }
        }

        return $headers;
    }
}