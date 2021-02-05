<?php

namespace YooKassa\Helpers;


class UUID
{
    /**
     * @return string
     * @throws \Exception
     */
    public static function v4()
    {
        $hexData  = bin2hex(Random::bytes(16));
        $parts    = str_split($hexData, 4);
        $parts[3] = '4' . substr($parts[3], 1);
        $parts[4] = '8' . substr($parts[4], 1);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s',
            $parts
        );
    }
}