<?php

namespace RunOpenCode\AbstractBuilder\Tests\Fixtures;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\LockableTrait;

class Dummy
{
    use LoggerAwareTrait {
      //  setLogger as pero;
    }

    use LockableTrait;
}
