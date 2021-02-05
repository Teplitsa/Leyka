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


use YooKassa\Client;

/**
 * Class UserAgent
 * @package YooKassa\Client
 */
class UserAgent
{
    const HEADER            = 'YM-User-Agent';
    const VERSION_DELIMITER = '/';
    const PART_DELIMITER    = ' ';

    private $_os = null;
    private $_php = null;
    private $_framework = null;
    private $_cms = null;
    private $_module = null;
    private $_sdk = null;

    /**
     * UserAgent constructor.
     */
    public function __construct()
    {
        if ($os = $this->defineOs()) {
            $this->setOs($os['name'], $os['version']);
        }
        if ($php = $this->definePhp()) {
            $this->setPhp($php['name'], $php['version']);
        }
        $this->setSdk('YooKassa.PHP', Client::SDK_VERSION);
    }

    /**
     * @return string
     */
    public function getHeaderString()
    {
        $result = array();

        $result[] = $this->getOs();
        $result[] = $this->getPhp();

        if ($string = $this->getFramework()) {
            $result[] = $string;
        }
        if ($string = $this->getCms()) {
            $result[] = $string;
        }
        if ($string = $this->getModule()) {
            $result[] = $string;
        }

        $result[] = $this->getSdk();

        return implode(self::PART_DELIMITER, $result);
    }

    /**
     * @return string
     */
    public function getOs()
    {
        return $this->_os;
    }

    /**
     * @param string $name
     * @param string $version
     */
    private function setOs($name, $version)
    {
        $this->_os = $this->createVersion($name, $version);
    }

    /**
     * @return string
     */
    public function getPhp()
    {
        return $this->_php;
    }

    /**
     * @param string $name
     * @param string $version
     */
    private function setPhp($name, $version)
    {
        $this->_php = $this->createVersion($name, $version);
    }

    /**
     * @return string|null
     */
    public function getFramework()
    {
        return $this->_framework;
    }

    /**
     * @param string $name
     * @param string $version
     */
    public function setFramework($name, $version)
    {
        $this->_framework = $this->createVersion($name, $version);
    }

    /**
     * @return null
     */
    public function getCms()
    {
        return $this->_cms;
    }

    /**
     * @param string $name
     * @param string $version
     */
    public function setCms($name, $version)
    {
        $this->_cms = $this->createVersion($name, $version);
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * @param string $name
     * @param string $version
     */
    public function setModule($name, $version)
    {
        $this->_module = $this->createVersion($name, $version);
    }

    /**
     * @return string
     */
    public function getSdk()
    {
        return $this->_sdk;
    }

    /**
     * @param string $name
     * @param string $version
     */
    private function setSdk($name, $version)
    {
        $this->_sdk = $this->createVersion($name, $version);
    }

    /**
     * Попытка определить систему
     * @return array
     */
    private function defineOs()
    {
        if (strtolower(substr(PHP_OS, 0, 5)) === 'linux') {
            if ($result = $this->parseSimpleLinuxRelease()) {
                return $result;
            } elseif ($result = $this->parseSmartLinuxRelease()) {
                return $result;
            }
        } else {
            return array( 'name' => php_uname('s'), 'version' => php_uname('r') );
        }

        return array( 'name' => 'Undefined', 'version' => '0.0.0' );
    }

    /**
     * Возвращает информацию о версии системы
     * Используется сложный вариант
     * @return array|null
     */
    private function parseSmartLinuxRelease()
    {
        $vars = array();

        if ($files = glob('/etc/*elease')) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $lines = array_filter(array_map(array($this, 'callbackSmartLinux'), file($file)));
                    if (is_array($lines)) {
                        foreach ($lines as $line) {
                            $vars[strtoupper($line[0])] = trim($line[1]);
                        }
                    }
                }
            }

            if (!empty($vars['NAME']) && !empty($vars['VERSION_ID'])) {
                return array('name' => $vars['NAME'], 'version' => $vars['VERSION_ID']);
            }
        }

        return null;
    }

    /**
     * @param string $line
     * @return array|bool
     */
    private static function callbackSmartLinux($line)
    {
        $parts = explode('=', $line);
        if (count($parts) !== 2) {
            return false;
        }
        $parts[1] = trim(str_replace(array('"', "'"), '', $parts[1]));
        return $parts;
    }

    /**
     * Возвращает информацию о версии системы
     * Используется простой вариант
     * @return array|null
     */
    private function parseSimpleLinuxRelease()
    {
        $vars = array();

        if ($files = glob('/etc/*elease')) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $data = array_map(array($this, 'callbackSimpleLinux'), file($file));
                    if (!empty($data)) {
                        $array = array_shift($data);
                        if (!empty($array) && is_array($array)) {
                            $vars = array_merge($vars, $array);
                        }
                    }
                }
            }
        }

        return !empty($vars['name']) && !empty($vars['version']) ? $vars : null;
    }

    /**
     * @param string $line
     * @return array
     */
    private static function callbackSimpleLinux($line)
    {
        $parse = array();
        preg_match('/(.+) release (.+) (.+)/iu', $line, $parts);
        if (!empty($parts[1])) {
            $parse['name'] = str_replace(' ', '.', trim($parts[1]));
        }
        if (!empty($parts[2])) {
            $parse['version'] = trim($parts[2]);
        }
        return $parse;
    }

    /**
     * Определение версии PHP
     * @return array
     */
    private function definePhp()
    {
        return array(
            'name'    => 'PHP',
            'version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION  . '.' . PHP_RELEASE_VERSION
        );
    }

    /**
     * Создание строки версии компонента
     * @param string $name
     * @param string $version
     * @return string
     */
    public function createVersion($name, $version)
    {
        return str_replace(array(self::PART_DELIMITER, self::VERSION_DELIMITER), '.', trim($name))
             . self::VERSION_DELIMITER
             . str_replace(array(self::PART_DELIMITER, self::VERSION_DELIMITER), '.', trim($version));
    }

}