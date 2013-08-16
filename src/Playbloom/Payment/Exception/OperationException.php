<?php

namespace Playbloom\Payment\Exception;

use Exception;

class OperationException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
