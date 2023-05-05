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

namespace YooKassa\Model;

interface LegInterface
{
    /**
     * Возвращает трёхбуквенный IATA-код аэропорта вылета
     * @return string Трёхбуквенный IATA-код аэропорта вылета
     */
    public function getDepartureAirport();

    /**
     * Возвращает трёхбуквенный IATA-код аэропорта прилёта
     * @return string Трёхбуквенный IATA-код аэропорта прилёта
     */
    public function getDestinationAirport();

    /**
     * Возвращает дату вылета в формате YYYY-MM-DD ISO 8601:2004
     * @return string Дата вылета в формате YYYY-MM-DD ISO 8601:2004
     */
    public function getDepartureDate();

    /**
     * Устанавливает трёхбуквенный IATA-код аэропорта вылета
     * @param string $value Трёхбуквенный IATA-код аэропорта вылета
     */
    public function setDepartureAirport($value);

    /**
     * Устанавливает трёхбуквенный IATA-код аэропорта прилёта
     * @param string $value Трёхбуквенный IATA-код аэропорта прилёта
     */
    public function setDestinationAirport($value);

    /**
     * Устанавливает дату вылета в формате YYYY-MM-DD ISO 8601:2004
     * @param \DateTime|string $value Дата вылета в формате YYYY-MM-DD ISO 8601:2004
     */
    public function setDepartureDate($value);
}
