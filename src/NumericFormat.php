<?php

namespace RyanChandler\F;

/** @internal */
enum NumericFormat: string
{
    case Binary = 'b';
    case Octal = 'o';
    case Hexadecimal = 'x';

    /**
     * Format the given integer value.
     */
    public function format(int $value): string
    {
        return match ($this) {
            self::Binary => decbin($value),
            self::Octal => decoct($value),
            self::Hexadecimal => dechex($value),
        };
    }
}
