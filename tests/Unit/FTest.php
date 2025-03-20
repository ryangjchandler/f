<?php

it('does not process anything if there are no placeholders', function () {
    expect(f('Hello, world!'))->toBe('Hello, world!');
});

it('processes a single plain placeholder', function () {
    expect(f('{}', 'Hello!'))->toBe('Hello!');
});

it('ignores escaped placeholders', function () {
    expect(f('Hello, \{}!'))->toBe('Hello, {}!');
});

it('processes placeholders based on the order that they are defined', function () {
    expect(f('Hello, {}! My name is {}!', 'world', 'Ryan'))->toBe('Hello, world! My name is Ryan!');
});

it('processes placeholders with positional argument references', function () {
    expect(f('Hello, {0}! My name is {1}, it is nice to meet you {0}!', 'world', 'Ryan'))->toBe('Hello, world! My name is Ryan, it is nice to meet you world!');
});

it('processes placeholders with named argument references', function () {
    expect(f('Hello, {world}! My name is {name}, it is nice to meet you {world}!', world: 'world', name: 'Ryan'))->toBe('Hello, world! My name is Ryan, it is nice to meet you world!');
});

it('processes placeholders with a mix of positional and named argument references', function () {
    expect(f('Hello, {0}! My name is {name}, it is nice to meet you {world}!', world: 'world', name: 'Ryan'))->toBe('Hello, world! My name is Ryan, it is nice to meet you world!');
});

it('throws an exception if a named placeholder is used with a list of positional arguments', function () {
    f('{name}', 'Ryan');
})->throws(InvalidArgumentException::class, 'Cannot use named placeholder [name] with a list of positional arguments.');

it('throws an exception if a positional placeholder is used without an argument', function () {
    f('{1}', 'Ryan');
})->throws(InvalidArgumentException::class, 'Missing argument for placeholder [1].');

it('throws an exception if a positional placeholder is used with named arguments, but the positional reference is not found', function () {
    f('{1}', name: 'Ryan');
})->throws(InvalidArgumentException::class, 'Missing argument for placeholder [1].');

it('throws an exception if a named placeholder is used without an argument', function () {
    f('{name}', world: 'world');
})->throws(InvalidArgumentException::class, 'Missing argument for placeholder [name].');

it('can format an integer as binary', function () {
    expect(f('{:b}', 42))->toBe('101010');
});

it('can format an integer as octal', function () {
    expect(f('{:o}', 42))->toBe('52');
});

it('can format an integer as hexadecimal', function () {
    expect(f('{:x}', 42))->toBe('2a');
});

it('throws an exception if a numeric format specifier is used without an integer argument', function () {
    f('{:b}', 'hello');
})->throws(InvalidArgumentException::class, 'Expected an integer for placeholder [{:b}], got [hello].');

it('can right-justify a placeholder', function () {
    expect(f('{:>10}', 'Hello'))->toBe('     Hello');
});

it('can left-justify a placeholder', function () {
    expect(f('{:<10}', 'Hello'))->toBe('Hello     ');
});

it('can justify a placeholder using a custom padding character', function () {
    expect(f('{:~>10}', 'Hello'))->toBe('~~~~~Hello');
});

it('can justify a placeholder using a named width specifier', function () {
    expect(f('{:>width$}', 'Hello', width: 10))->toBe('     Hello');
});

it('throws an exception if a named width specifier is used without a valid argument', function () {
    f('{:>width$}', 'Hello');
})->throws(InvalidArgumentException::class, 'Missing justification width argument [width] for placeholder [{:>width$}].');

it('throws an exception if a named width specified is not an integer', function () {
    f('{:>width$}', 'Hello', width: 'hello');
})->throws(InvalidArgumentException::class, 'Expected an integer for justification width argument [width] for placeholder [{:>width$}], got [hello].');
