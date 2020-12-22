<?php

namespace MixplatClient\HttpClient;

use MixplatClient\Method\MixplatMethod;
use MixplatClient\MixplatException;

class SimpleHttpClient implements HttpClientInterface
{

    /**
     * @param \MixplatClient\Configuration $config
     * @param MixplatMethod $method
     * @return mixed|void
     */
    public function request($config, $method)
    {
        $params = $method->getParams($config);

        $context = stream_context_create
        (
            array
            (
                'http' => array
                (
                    'method' => 'POST',
                    'timeout' => $config->clientTimeout,
                    'header' => 'Content-type: application/json',
                    'content' => json_encode($params)
                )
            )
        );

        $response = file_get_contents($config->apiUrl . $method->getMethod(), false, $context);

        if (empty($response)) {
            throw new MixplatException('Empty server response.');
        }

        $responseParameters = json_decode($response, true);

        if (empty($responseParameters)) {
            throw new MixplatException('Empty response parameters.');
        }

        return $responseParameters;

    }
}
