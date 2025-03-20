# `f`

`f()` is an alternative to string concatenation and PHP's [`sprintf()`](https://www.php.net/manual/en/function.sprintf.php) function. It takes inspiration from how Rust handles string formatting with the `format!()` macro.

It doesn't currently do everything that `sprintf()` does, but it does enough to be useful.

## Installation

You can install the package using Composer:

```sh
composer require ryangjchandler/f
```

## Usage

Once installed, the package will provide a global `f()` function that you can start using straight away.

```php
f('Hello, {}', 'Ryan');
```

If you've already got an `f()` function registered in the global namespace, or despise global helper functions, you can use the `RyanChandler\F\F` class instead.

```php
use RyanChandler\F\F;

F::format('Hello, {}', 'Ryan');
```

### Placeholder Syntax

The `{}` syntax you see above is known as a placeholder. This is where the arguments you pass to `f()` will be injected into the string.

| Placeholder | Description | Example | Result |
| - | - | - | - |
| `{}` | References an argument based on the position of the placeholder in the format string. | `f('Hello, {}. I am {}.', 'world', 'Ryan')` | `Hello, world. I am Ryan.` |
| `{2}` | References the 3rd argument (0-based indexing). | `f('{}, {}, {}, {2}', 1, 2, 3)` | `1, 2, 3, 3` |
| `{name}` | References a named argument. | `f('Hello, {name}', name: "Ryan")` | `Hello, Ryan` |
| `{:b}` | Formats an integer as binary. | `f('{:b}', 42)` | `101010` |
| `{:x}` | Formats an integer as hexadecimal. | `f('{:x}', 42)` | `2a` |
| `{:o}` | Formats an integer as octal. | `f('{:o}', 42)` | `52` |
| `{:>10}` | Right-justifies the argument to a width of `10` using a single space. | `f('{:>10}', 'Hello')` | <code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hello</code> |
| `{:<10}` | Left-justifies the argument to a width of `10` using a single space. | `f('{:>10}', 'Hello')` | <code>Hello&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</code> |
| `{:0>10}` | Right-justifies the argument to a width of `10` using a custom character `0`. | `f('{:0>10}', '12345')` | `0000012345` |
| `{:0<10}` | Left-justifies the argument to a width of `10` using a custom character `0`. | `f('{:0>10}', '12345')` | `1234500000` |
| `{:>width$}` | Right-justifies the argument to a width defined by the named argument `width`. | `f('{:>width$}', 'Hello', width: 7)` | <code>&nbsp;&nbsp;Hello</code> |

## Contributing

All contributions are appreciated and welcome. Please refer to the [CONTRIBUTING](./CONTRIBUTING.md) document for more information on how to contribute.

## Credits

* [Ryan Chandler](https://github.com/ryangjchandler)
* All contributors

## License

This project is licensed under the [MIT license](./LICENSE). Please refer to the [LICENSE](./LICENSE) document for more information.
