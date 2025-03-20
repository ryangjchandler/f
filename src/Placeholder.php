<?php

namespace RyanChandler\F;

/** @internal */
class Placeholder
{
    /**
     * @param array{
     *     0: array{0: string, 1: int},
     *     escaped: array{0: string|null, 1: int},
     *     specifier: array{0: string|null, 1: int},
     *     target: array{0: string|null, 1: int},
     *     format: array{0: string|null, 1: int},
     *     numeric: array{0: string|null, 1: int},
     *     justify: array{0: string|null, 1: int},
     *     pad: array{0: string|null, 1: int},
     *     dir: array{0: string|null, 1: int},
     *     width: array{0: string|null, 1: int},
     *     digit: array{0: string|null, 1: int},
     *     named: array{0: string|null, 1: int},
     * } $matches
     */
    public function __construct(protected array $matches) {}

    /**
     * Get the start position of the placeholder.
     */
    public function offset(): int
    {
        return $this->matches[0][1];
    }

    /**
     * Get the length of the placeholder.
     */
    public function len(): int
    {
        return strlen($this->matches[0][0]);
    }

    /**
     * Determine whether or not the placeholder is escaped by a backslash.
     */
    public function isEscaped(): bool
    {
        return $this->matches['escaped'][0] !== null;
    }

    /**
     * Get the raw placeholder string.
     */
    public function raw(): string
    {
        return $this->matches[0][0];
    }

    /**
     * Determine whether or not the placeholder has a specifier.
     */
    public function hasSpecifier(): bool
    {
        return $this->matches['specifier'][0] !== null;
    }

    /**
     * Determine whether or not the placeholder has a target.
     */
    public function hasTarget(): bool
    {
        return $this->matches['target'][0] !== null;
    }

    /**
     * Determine whether or not the placeholder has a format.
     */
    public function hasFormat(): bool
    {
        return $this->matches['format'][0] !== null;
    }

    /**
     * Determine whether or not the placeholder has a numeric format.
     */
    public function hasNumericFormat(): bool
    {
        return $this->hasFormat() && $this->matches['numeric'][0] !== null;
    }

    /**
     * Determine whether or not the placeholder has a justification format.
     */
    public function hasJustificationFormat(): bool
    {
        return $this->hasFormat() && $this->matches['justify'][0] !== null;
    }

    /**
     * Determine whether or not the placeholder has a specific pad character.
     */
    public function hasPadCharacter(): bool
    {
        return $this->hasFormat() && $this->matches['pad'][0] !== null;
    }

    /**
     * Get the target for the placeholder. This is either an integer or a string.
     */
    public function target(): string|int
    {
        $target = $this->matches['target'][0];

        if (is_numeric($target)) {
            return (int) $target;
        }

        return $target;
    }

    /**
     * Get the numeric format for the placeholder.
     */
    public function numericFormat(): ?NumericFormat
    {
        if (! $this->hasNumericFormat()){
            return null;
        }

        return NumericFormat::tryFrom($this->matches['numeric'][0]);
    }

    /**
     * Get the justification character for the placeholder.
     */
    public function justifyCharacter(): string
    {
        if ($this->matches['pad'][0] !== null) {
            return $this->matches['pad'][0];
        }

        return ' ';
    }

    /**
     * Get the justification direction for the placeholder.
     * 
     * @return \STR_PAD_LEFT | \STR_PAD_RIGHT
     */
    public function justifyDirection(): int
    {
        return $this->matches['dir'][0] === '<' ? \STR_PAD_RIGHT : \STR_PAD_LEFT;
    }

    /**
     * Get the width specifier for the placeholder.
     */
    public function justifyWidth(): string | int
    {
        if ($this->matches['named'][0] !== null) {
            return $this->matches['named'][0];
        }

        return (int) $this->matches['digit'][0];
    }
}
