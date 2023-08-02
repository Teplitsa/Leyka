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

namespace YooKassa\Helpers;

/**
 * Класс для формирования тега 1162 на основе кода в формате Data Matrix
 *
 * @example 04-product-code.php 6 6 Вариант через метод
 * @example 04-product-code.php 14 6 Вариант через массив
 *
 * @link https://github.com/yoomoney/yookassa-sdk-php/blob/master/lib/Helpers/ProductCode.php
 *
 * @package YooKassa
 */
class ProductCode
{
    /** Код типа маркировки DataMatrix */
    const PREFIX_DATA_MATRIX = '444D';

    /** @var string Код типа маркировки */
    private $prefix;

    /**
     * @var string Global Trade Item Number
     * Глобальный номер товарной продукции в единой международной базе товаров GS1 https://ru.wikipedia.org/wiki/GS1
     * @example 04630037591316
     */
    private $gtin;

    /**
     * @var string Серийный номер товара
     * @example sgEKKPPcS25y5
     */
    private $serial;

    /** @var string Сформированный тег 1162. Формат: hex([prefix]+gtin+serial)
     * @example 04 36 03 BE F5 14 73  67  45  4b  4b  50  50  63  53  32  35  79  35
     */
    private $result;

    /** @var bool Флаг использования кода типа маркировки */
    private $usePrefix = false;

    /**
     * ProductCode constructor.
     * @param string|null $codeDataMatrix Строка, расшифрованная из QR-кода
     * @param bool|string $usePrefix Нужен ли код типа маркировки в результате
     */
    public function __construct($codeDataMatrix=null, $usePrefix=false)
    {
        $this->preparePrefix($usePrefix);

        if (!empty($codeDataMatrix)) {
            if ($this->parseCodeMatrixData($codeDataMatrix)) {
                $this->result = $this->calcResult();
            }
        }
    }

    /**
     * Возвращает Код типа маркировки
     * @return string Код типа маркировки
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Устанавливает Код типа маркировки
     * @param string|int $prefix Код типа маркировки
     * @return ProductCode
     */
    public function setPrefix($prefix)
    {
        if ($prefix === null || $prefix === '') {
            $this->prefix = null;
            return $this;
        }

        if (is_int($prefix)) {
            $prefix = dechex($prefix);
        }
        $this->prefix = str_pad($prefix, 4, '0', STR_PAD_LEFT);

        return $this;
    }

    /**
     * Возвращает Глобальный номер товарной продукции
     * @return string Глобальный номер товарной продукции
     */
    public function getGtin()
    {
        return $this->gtin;
    }

    /**
     * Устанавливает Глобальный номер товарной продукции
     * @param string $gtin Глобальный номер товарной продукции
     * @return ProductCode
     */
    public function setGtin($gtin)
    {
        if ($gtin === null || $gtin === '') {
            $this->gtin = null;
        } else {
            $this->gtin = $gtin;
        }
        return $this;
    }

    /**
     * Возвращает Серийный номер товара
     * @return string Серийный номер товара
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Устанавливает Серийный номер товара
     * @param string $serial Серийный номер товара
     * @return ProductCode
     */
    public function setSerial($serial)
    {
        if ($serial === null || $serial === '') {
            $this->prefix = null;
        } else {
            $this->serial = $serial;
        }
        return $this;
    }

    /**
     * Возвращает Сформированный тег 1162.
     * @return string Сформированный тег 1162.
     */
    public function getResult()
    {
        if (!$this->result) {
            $this->result = $this->calcResult();
        }

        return $this->result;
    }

    /**
     * Возвращает флаг использования кода типа маркировки
     * @return bool
     */
    public function isUsePrefix()
    {
        return $this->usePrefix;
    }

    /**
     * Устанавливает флаг использования кода типа маркировки
     * @param bool $usePrefix Флаг использования кода типа маркировки
     * @return ProductCode
     */
    public function setUsePrefix($usePrefix)
    {
        $this->usePrefix = (bool)$usePrefix;
        return $this;
    }

    /**
     * Формирует тег 1162.
     * @return string|null Сформированный тег 1162.
     */
    public function calcResult()
    {
        $result = '';

        if (!$this->validate()) {
            return $result;
        }

        if ($this->isUsePrefix()) {
            $result = $this->getPrefix() ?: self::PREFIX_DATA_MATRIX;
        }

        $result .= $this->numToHex($this->getGtin());
        $result .= $this->strToHex($this->getSerial());

        return $this->chunkStr($result);
    }

    /**
     * Устанавливает prefix и usePrefix в зависимости от входящего параметра
     * @param mixed $usePrefix Код типа маркировки или bool
     */
    private function preparePrefix($usePrefix)
    {
        if ($usePrefix) {
            $this->setUsePrefix(true);
            if (is_string($usePrefix) || is_int($usePrefix)) {
                $this->setPrefix($usePrefix);
            } else {
                $this->setPrefix(self::PREFIX_DATA_MATRIX);
            }
        } else {
            $this->setUsePrefix(false);
            $this->setPrefix(null);
        }
    }

    /**
     * Извлекает необходимые данные из строки, расшифрованной из QR-кода и устанавливает соответствующие свойства.
     * Возвращает результат в виде bool
     * @param string $codeDataMatrix Строки, расшифрованная из QR-кода
     * @return false
     */
    private function parseCodeMatrixData($codeDataMatrix)
    {
        $string = preg_replace('#91(.+)92(.+)#i', '', $codeDataMatrix);
        preg_match('#01(\d{14})21(.+)#i', $string, $matches);

        $this->setGtin(!empty($matches[1]) ? $matches[1] : null);
        $this->setSerial(!empty($matches[2]) ? $matches[2] : null);

        return $this->validate();
    }

    /**
     * Проверяет заполненность необходимых свойств
     * @return bool
     */
    public function validate()
    {
        return $this->getGtin() && $this->getSerial();
    }

    /**
     * Разбивает пробелами строку на пары символов и переводит в верхний регистр
     * @param string $string Подготовленная к разбиению строка
     * @return string
     */
    private function chunkStr($string)
    {
        return strtoupper(trim(chunk_split($string, 2, ' ')));
    }

    /**
     * Переводит десятичное число в шестнадцатеричный вид и дополняет нулями до 12 символов слева
     * @param string $string Входящее число (Глобальный номер товарной продукции)
     * @return string
     */
    private function numToHex($string)
    {
        return str_pad($this->base_convert($string), 12, '0', STR_PAD_LEFT);
    }

    /**
     * Переводит число из одной системы исчисления в другую
     * Замена dechex() для 32-битных версии PHP
     *
     * @param string $numString
     * @param int $fromBase
     * @param int $toBase
     * @return string
     */
    private function base_convert($numString, $fromBase=10, $toBase=16)
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $toString = substr($chars, 0, $toBase);

        $length = strlen($numString);
        $result = '';
        $number = array();

        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, substr($numString, $i, 1));
        }

        do {
            $divide = 0;
            $newLen = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $fromBase + $number[$i];
                if ($divide >= $toBase) {
                    $number[$newLen++] = (int)($divide / $toBase);
                    $divide = $divide % $toBase;
                } elseif ($newLen > 0) {
                    $number[$newLen++] = 0;
                }
            }
            $length = $newLen;
            $result = substr($toString, $divide, 1) . $result;
        } while ($newLen != 0);

        return $result;
    }

    /**
     * Переводит строку в шестнадцатеричный вид
     * @param string $string Входящая строка (Серийный номер товара)
     * @return string
     */
    private function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return $hex;
    }

    /**
     * Переводит строку из шестнадцатеричного вида в обычный
     * Нужен для тестирования
     * @param string $hex Входящая строка в шестнадцатеричном виде
     * @return string
     */
    private function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    /**
     * Приводит объект к строке
     * @return string
     */
    public function __toString()
    {
        return $this->getResult();
    }

}
