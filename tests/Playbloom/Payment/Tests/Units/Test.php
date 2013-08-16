<?php

namespace Playbloom\Payment\Tests\Units;

use mageekguy\atoum\test as AtoumTest,
    mageekguy\atoum\factory;

abstract class Test extends AtoumTest
{
    public function __construct(factory $factory = null)
    {
        parent::__construct($factory);
    }
}
