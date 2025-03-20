<?php

// If you're already defining a function called `f`, you're a real one.

use RyanChandler\F\F;

if (function_exists('f')) {
    return;
}

/**
 * Return a formatted string.
 *
 * @throws \InvalidArgumentException
 */
function f(string $format, mixed ...$args): string
{
    return F::format($format, ...$args);
}
