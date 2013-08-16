<?php

namespace Playbloom\Payment\Operation;

use InvalidArgumentException;

/**
 * Card operation identifier
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class OperationId
{
    /**
     * @var mixed Scalar type
     */
    private $value;

    /**
     * Constructor
     *
     * @param mixed $value Scalar type
     */
    public function __construct($value)
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf('Invalid "%s" type', gettype($value)));
        }

        $this->value = $value;
    }

    /**
     * Get the string representatoin
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Get the identifier value
     *
     * @return mixed Scalar type
     */
    public function getValue()
    {
        return $this->value;
    }
}
