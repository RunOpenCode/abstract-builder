<?php

namespace RunOpenCode\AbstractBuilder\Tests\Fixtures;

use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;

class ReflectiveMessageBuilder extends ReflectiveAbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function getObjectFqcn()
    {
        return Message::class;
    }
}
