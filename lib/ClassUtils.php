<?php

namespace DTL\Spryker\Fixtures;

class ClassUtils
{
    public static function normalize($classFqn)
    {
        if (substr($classFqn, 0,1) === '\\') {
            return $classFqn;
        }

        return '\\' . $classFqn;
    }
}
