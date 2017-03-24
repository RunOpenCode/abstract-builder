<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Tests;

use PHPUnit\Framework\TestCase;
use RunOpenCode\AbstractBuilder\Tests\Fixtures\Message;
use RunOpenCode\AbstractBuilder\Tests\Fixtures\ReflectiveMessageBuilder;

/**
 * Class ReflectiveAbstractBuilderTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests
 */
class ReflectiveAbstractBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function itReflectsParameters()
    {
        $message = ReflectiveMessageBuilder::createBuilder()
            ->setId(1)
            ->setMessage('Some message')
            ->setTimestamp(new \DateTime('now'))
            ->build();

        $this->assertBuildedMessage($message);
    }

    private function assertBuildedMessage(Message $message)
    {
        $this->assertEquals(1, $message->getId());
        $this->assertEquals('Some message', $message->getMessage());
        $this->assertInstanceOf(\DateTime::class, $message->getTimestamp());
    }
}
