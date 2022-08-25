<?php

namespace Dashamail\ApiWrapper;

class DashamailApi {

    protected $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }


    protected function getApiHost() {
        return sprintf('http://api.dashamail.com/');
    }


    protected function decodeJson($json) {
        return json_decode($json);
    }


    protected function getClientIp() {
        $result = '';

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $result = $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (preg_match('/([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.' .
            '([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}/', $result, $match)) {
            return $match[0];
        }

        return $result;
    }


    protected function callMethod($methodName, $params=[]) {

        $url = '?method='.$methodName.'&';
        $params = array_merge(['api_key' => $this->apiKey], (array) $params);
        $content = http_build_query($params);
        $contextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded;charset=utf-8',
                'content' => $content,
            ],
            'ssl' => [
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            ]
        ];
        $context = stream_context_create($contextOptions);
        $host = $this->getApiHost();
        $result = @file_get_contents($host.$url, false, $context);

        return $result;
    }


    public function __call($name, $arguments) {

        if (!is_array($arguments) || 0 === count($arguments)) {
            $params = [];
        } else {
            $params = $arguments[0];
        }

        return $this->callMethod($name, $params);
    }


    public function getLists(array $params) {
        return $this->callMethod('lists.get', $params);
    }

    public function addList(array $params) {
        return $this->callMethod('lists.add', $params);
    }

    public function addMember(array $params) {
        return $this->callMethod('lists.add_member', $params);
    }

    public function getMember(array $params) {
        return $this->callMethod('lists.get_members', $params);
    }

    public function addMerge(array $params) {
        return $this->callMethod('lists.add_merge', $params);
    }

    public function deleteMerge(array $params) {
        return $this->callMethod('lists.delete_merge', $params);
    }
}