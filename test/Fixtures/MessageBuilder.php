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
            'timestamp' => new \DateTime('now'),
            'count' => 0
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjectFqcn()
    {
        return Message::class;
    }

    public function getMessage()
    {
        return $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32['message'];
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return 'getter_invoked';
    }

    /**
     * @param int $count
     * @return string
     */
    public function setCount($count)
    {
        $this->__doSet('count', $count + 10);
        return $this;
    }

}
