<?php

namespace MixplatClient\Notify;

use MixplatClient\Configuration;

class MixplatNotify
{
    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        return array();
    }

    /**
     * @return MixplatNotify
     */
    public function setParams($vars)
    {
        foreach ($vars as $key => $var) {
            $attribute = $this->camelize($key);
            $this->$attribute = $var;
        }

        return $this;
    }

    /**
     * @param Configuration $config
     * @return bool
     */
    public function checkSignature($config)
    {
        return false;
    }

    /**
     * @param string $signatureString
     * @return string
     */
    protected function encryptSignature($signatureString)
    {
        return md5($signatureString);
    }

    protected function camelize($string)
    {
        return lcfirst(preg_replace_callback(
            "/(^|_)([a-z])/",
            function ($m) {
                return strtoupper("$m[2]");
            },
            $string
        ));
    }

}
