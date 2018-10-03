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

/**
 * Класс хэлпера для генерации случайных значений, используется в тестах
 *
 * @package YandexCheckout\Helpers
 */
class Random
{
    /**
     * Возвращает рандомное целое число. По умолчанию возвращает число от нуля до PHP_INT_MAX.
     * @param int|null $min Минимально возможное значение
     * @param int|null $max Максимально возможное значение
     * @param bool $useBest Использовать ли функцию random_int если она доступна
     * @return int Рандомное целое число
     * @throws \Exception
     */
    public static function int($min = null, $max = null, $useBest = true)
    {
        if ($min === null) {
            $min = 0;
        }
        if ($max === null) {
            $max = PHP_INT_MAX;
        }
        if (function_exists('random_int') && $useBest) {
            return random_int($min, $max);
        } else {
            return mt_rand($min, $max);
        }
    }

    /**
     * Возвращает рандомное число с плавающей точкой. По умолчанию возвращает значение в промежутке от нуля до едениы.
     * @param float|null $min Минимально возможное значение
     * @param float|null $max Максимально возможное значение
     * @param bool $useBest Использовать ли функцию random_int если она доступна
     * @return float Рандомное число с плавающей точкой
     * @throws \Exception
     */
    public static function float($min = null, $max = null, $useBest = true)
    {
        $random = self::int(null, null, $useBest) / PHP_INT_MAX;
        if ($min === null) {
            $min = 0.0;
        }
        if ($max === null) {
            return $random + $min;
        }
        return ($random * ($max - $min)) + $min;
    }

    /**
     * Возвращает строку из рандомных символов
     * @param int $length Длина возвращаемой строки, или минимальная длина, если передан парамтр $maxLength
     * @param int|null $maxLength Если параметр не равен null, возвращает сроку длиной от $length до $maxLength
     * @param string|array|null $characters Строка или массив используемых в строке символов
     * @param bool $useBest Использовать ли функцию random_int если она доступна
     * @return string Строка, состоящая из рандомных символов
     * @throws \Exception
     */
    public static function str($length, $maxLength = null, $characters = null, $useBest = true)
    {
        $result = '';
        if ($maxLength !== null) {
             if (is_string($maxLength)) {
                 $characters = $maxLength;
             } else {
                 $length = self::int($length, $maxLength, $useBest);
             }
        }
        if ($characters === null) {
            for ($i = 0; $i < $length; $i++) {
                $chr = chr(self::int(32, 125, $useBest));
                $result .= $chr;
            }
        } else {
            for ($i = 0; $i < $length; $i++) {
                $chr = $characters[self::int(0, strlen($characters) - 1, $useBest)];
                $result .= $chr;
            }
        }
        return $result;
    }

    /**
     * Возвращает строку, состоящую из символов '0123456789abcdef'
     * @param int $length Длина возвращаемой строки
     * @param bool $useBest Использовать ли функцию random_int если она доступна
     * @return string Строка, состоящая из рандомных символов
     * @throws \Exception
     */
    public static function hex($length, $useBest = true)
    {
        return self::str($length, '0123456789abcdef', $useBest);
    }

    /**
     * Возвращает рандомную последовательность байт
     * @param int $length Длина возвращаемой строки
     * @param bool $useBest Использовать ли функцию random_int если она доступна
     * @return string Строка, состоящая из рандомных символов
     * @throws \Exception
     */
    public static function bytes($length, $useBest = true)
    {
        if (function_exists('random_bytes') && $useBest) {
            $result = random_bytes($length);
        } else {
            $result = '';
            for ($i = 0; $i < $length; $i++) {
                $chr = chr(self::int(0, 255));
                $result .= $chr;
            }
        }
        return $result;
    }

    /**
     * Возвращает рандомное значение из переданного массива
     * @param array $values Массив источник данных
     * @param bool $useBest Использовать ли функцию random_int если она доступна
     * @return mixed Случайное значение из переданного массива
     * @throws \Exception
     */
    public static function value(array $values, $useBest = true)
    {
        return $values[self::int(0, count($values) - 1, $useBest)];
    }

    /**
     * Возвращает рандомное буллево значение
     * @return bool Либо true либо false, одно из двух
     * @throws \Exception
     */
    public static function bool()
    {
        return self::int(0, 1) === 1;
    }
}
