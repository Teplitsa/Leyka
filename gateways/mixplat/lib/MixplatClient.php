<?php

namespace MixplatClient;

use MixplatClient\HttpClient\HttpClientInterface;
use MixplatClient\Method\MixplatMethod;

class MixplatClient
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Configuration $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param MixplatMethod $method
     */
    public function request($method)
    {
        return $this->httpClient->request($this->config, $method);
    }

}
