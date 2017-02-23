<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Tests\Fixtures;

use RunOpenCode\AbstractBuilder\AbstractBuilder;

/**
 * Class MessageBuilder
 *
 * @package RunOpenCode\AbstractBuilder\Test\Fixtures
 */
class MessageBuilder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function configureParameters()
    {
        return [
            'id' => null,
            'message' => null,
            'timestamp' => new \DateTime('now')
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjectFqcn()
    {
        return Message::class;
    }

}
