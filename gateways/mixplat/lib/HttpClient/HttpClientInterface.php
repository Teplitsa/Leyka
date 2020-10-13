<?php

namespace MixplatClient\HttpClient;

use MixplatClient\Configuration;
use MixplatClient\Method\MixplatMethod;

interface HttpClientInterface
{
    /**
     * @param Configuration $config
     * @param MixplatMethod $method
     * @return mixed
     */
    public function request($config, $method);
}
