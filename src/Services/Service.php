<?php

namespace Audit\Services;

class Service
{
    /**
     * @return static
     */
    public static function getSingleton(): self
    {
        //@todo Ativar cache de acordo com business
        return new static();
    }
}