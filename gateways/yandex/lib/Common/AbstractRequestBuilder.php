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

namespace YooKassa\Common;

use YooKassa\Common\Exceptions\InvalidPropertyException;
use YooKassa\Common\Exceptions\InvalidRequestException;

/**
 * Базовый класс билдера запросов
 *
 * @package YooKassa\Common
 */
abstract class AbstractRequestBuilder
{
    /**
     * @var AbstractRequest Инстанс собираемого запроса
     */
    protected $currentObject;

    /**
     * Конструктор, инициализирует пустой запрос, который в будущем начнём собирать
     */
    public function __construct()
    {
        $this->currentObject = $this->initCurrentObject();
    }

    /**
     * Инициализирует пустой запрос
     * @return AbstractRequest Инстанс запроса который будем собирать
     */
    abstract protected function initCurrentObject();

    /**
     * Строит запрос, валидирует его и возвращает, если все прошло нормально
     * @param array $options Массив свойств запроса, если нужно их установить перед сборкой
     * @return AbstractRequest Инстанс собранного запроса
     *
     * @throws InvalidRequestException Выбрасывается если при валидации запроса произошла ошибка
     * @throws InvalidPropertyException Выбрасывается если не удалось установить один из параметров, переданныч в
     * массиве настроек
     */
    public function build(array $options = null)
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
        try {
            $this->currentObject->clearValidationError();
            if (!$this->currentObject->validate()) {
                throw new InvalidRequestException($this->currentObject);
            }
        } catch (InvalidRequestException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidRequestException($this->currentObject, 0, $e);
        }
        $result = $this->currentObject;
        $this->currentObject = $this->initCurrentObject();
        return $result;
    }

    /**
     * Устанавливает свойства запроса из массива
     * @param array|\Traversable $options Массив свойств запроса
     * @return AbstractRequestBuilder Инстанс текущего билдера запросов
     *
     * @throws \InvalidArgumentException Выбрасывается если аргумент не массив и не итерируемый объект
     * @throws InvalidPropertyException Выбрасывается если не удалось установить один из параметров, переданныч
     * в массиве настроек
     */
    public function setOptions($options)
    {
        if (empty($options)) {
            return $this;
        }
        if (!is_array($options) && !($options instanceof \Traversable)) {
            throw new \InvalidArgumentException('Invalid options value in setOptions method');
        }
        foreach ($options as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($this, $method)) {
                $this->{$method} ($value);
            } else {
                $property = str_replace('.', '_', $property);
                $field = implode('', array_map('ucfirst', explode('_', $property)));
                $method = 'set' . ucfirst($field);
                if (method_exists($this, $method)) {
                    $this->{$method} ($value);
                }
            }
        }
        return $this;
    }
}
