<?php

namespace RyanChandler\F;

use Stringable;

/** @internal */
class Processor implements Stringable
{
    /**
     * The arguments passed to `f()`.
     */
    protected array $args = [];

    const PARSER = <<<'REGEX'
    /(?<escaped>\\)?{(?<specifier>
        (?<target>([0-9][0-9]*)|([a-zA-Z][a-zA-Z_0-9]*))?
        (?<format>:(
            (?<numeric>b|o|x)
          | (?<justify>
                (?<pad>.)?
                    (?<dir><|>)
                    (?<width>
                        (?<digit>\d+)
                      | ((?<named>[a-zA-Z_][a-zA-Z_0-9]*)\$)
                    )
            )
        ))?
    )?}/Ux
    REGEX;

    public function __construct(
        protected string $format,
        mixed ...$args,
    ) {
        $this->args = $args;
    }

    /**
     * Get all placeholders found in the format string.
     *
     * @return Placeholder[]
     */
    public function placeholders(): array
    {
        $result = preg_match_all(self::PARSER, $this->format, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

        if ($result === false || $result === 0) {
            return [];
        }

        return array_map(static fn (array $match): Placeholder => new Placeholder($match), $matches);
    }

    /**
     * Generate a formatted string from the format string and arguments.
     *
     * @throws \InvalidArgumentException
     */
    public function __toString(): string
    {
        $placeholders = $this->placeholders();

        // If there are no placeholders, we can just return the format string as-is.
        if ($placeholders === []) {
            return $this->format;
        }

        // We first need to split the format string into parts, based on the position of the placeholders.
        // This will give us a list of non-placeholder strings that we can then process. The idea here is
        // that there should only ever be a single placeholder between each part.
        $parts = [];
        $end = 0;

        foreach ($placeholders as $placeholder) {
            $parts[] = substr($this->format, $end, $placeholder->offset() - $end);
            $end = $placeholder->offset() + $placeholder->len();
        }

        // If the end of the last placeholder is less than the length of the format string,
        // we need to append the remaining part to the parts array.
        if ($end < strlen($this->format)) {
            $parts[] = substr($this->format, $end);
        }

        // Now that we've got the respective parts, we can start to actually process the placeholders.
        // We don't need to handle placeholders that are located at the start of the format string,
        // as the `substr()` call above will insert an empty string at the start of the `$parts` array.
        $result = '';

        foreach ($placeholders as $index => $placeholder) {
            $result .= array_shift($parts);

            // If the placeholder is escaped, we can just remove the leading backslash,
            // append it to the result, and continue to the next placeholder.
            if ($placeholder->isEscaped()) {
                $result .= substr($placeholder->raw(), 1);

                continue;
            }

            // If the placeholder has a specifier, e.g. `{0}` or `{name}`, we need to grab that from the arguments.
            $replacement = $this->resolveTarget($placeholder, $index);

            if (! $placeholder->hasFormat()) {
                $result .= $replacement;

                continue;
            }

            if ($placeholder->hasNumericFormat()) {
                if (! is_int($replacement)) {
                    throw new \InvalidArgumentException("Expected an integer for placeholder [{$placeholder->raw()}], got [{$replacement}].");
                }

                $replacement = $placeholder->numericFormat()->format($replacement);
            }

            if ($placeholder->hasJustificationFormat()) {
                $replacement = str_pad(
                    $replacement,
                    $this->resolveJustifyWidth($placeholder),
                    $placeholder->justifyCharacter(),
                    $placeholder->justifyDirection()
                );
            }

            $result .= $replacement;
        }

        // Finally, we need to append any remaining parts to the result.
        $result .= implode('', $parts);

        return $result;
    }

    /**
     * Resolve the value of a placeholder target.
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveTarget(Placeholder $placeholder, int $index): mixed
    {
        if ($placeholder->hasTarget()) {
            $target = $placeholder->target();

            // If the arguments passed to `f()` are a list, we can't use a named specifier like `{name}`,
            // so we need to throw an exception as this is user-error.
            if (! is_int($target) && array_is_list($this->args)) {
                throw new \InvalidArgumentException("Cannot use named placeholder [{$target}] with a list of positional arguments.");
            }

            // If the specifier is a positional reference and we have a list of arguments, we can just grab the value.
            if (array_is_list($this->args) && is_int($target)) {
                if (! isset($this->args[$target])) {
                    throw new \InvalidArgumentException("Missing argument for placeholder [{$target}].");
                }

                return $this->args[$target];
            }

            // If the specifier is a positional reference but we don't have a list of arguments, we need to convert
            // the positional reference to a named reference.
            if (is_int($target) && ! isset($this->args[$target])) {
                $key = array_keys($this->args)[$target] ?? false;

                if ($key === false) {
                    throw new \InvalidArgumentException("Missing argument for placeholder [{$target}].");
                }

                return $this->args[$key];
            }

            // Otherwise, we should have a named reference and a list of named arguments.
            return $this->args[$target] ?? throw new \InvalidArgumentException("Missing argument for placeholder [{$target}].");
        }

        // If there's no specifier, we can just grab the value from the list of arguments.
        return $this->args[$index] ?? throw new \InvalidArgumentException("Missing argument for placeholder [{$index}].");
    }

    /**
     * Resolve the width for a justification format.
     * 
     * @throws \InvalidArgumentException
     */
    protected function resolveJustifyWidth(Placeholder $placeholder): int
    {
        $specifier = $placeholder->justifyWidth();

        if (is_int($specifier)) {
            return $specifier;
        }

        $width = $this->args[$specifier] ?? false;

        if ($width === false) {
            throw new \InvalidArgumentException("Missing justification width argument [{$specifier}] for placeholder [{$placeholder->raw()}].");
        }

        if (! is_int($width)) {
            throw new \InvalidArgumentException("Expected an integer for justification width argument [{$specifier}] for placeholder [{$placeholder->raw()}], got [{$width}].");
        }

        return $width;
    }
}
