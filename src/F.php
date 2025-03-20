<?php

namespace RyanChandler\F;

class F
{
    /**
     * Return a formatted string.
     * 
     * @throws \InvalidArgumentException
     */
    public static function format(string $format, mixed ...$args): string
    {
        return (new Processor($format, ...$args))->__toString();
    }
}
