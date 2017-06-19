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
use RunOpenCode\AbstractBuilder\AbstractBuilder;
use RunOpenCode\AbstractBuilder\Tests\Fixtures\Message;
use RunOpenCode\AbstractBuilder\Tests\Fixtures\MessageBuilder;

/**
 * Class AbstractBuilderTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests
 */
class AbstractBuilderTest extends TestCase
{
    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public function constructorThrowsExceptionWhenThereAreNoParams()
    {
        $builder = new class extends AbstractBuilder {

            /**
             * {@inheritdoc}
             */
            protected function configureParameters()
            {
                return [];
            }

            /**
             * {@inheritdoc}
             */
            protected function getObjectFqcn()
            {
                return Message::class;
            }
        };
    }

    /**
     * @test
     */
    public function itBuilds()
    {
        $message = MessageBuilder::createBuilder()
            ->setId(1)
            ->setMessage('Some message')
            ->build();

        $this->assertBuildedMessage($message);
    }

    /**
     * @test
     */
    public function itBuildViaInvoke()
    {
        $builder = MessageBuilder::createBuilder()
            ->setId(1)
            ->setMessage('Some message');

        $this->assertBuildedMessage($builder());
    }

    /**
     * @test
     */
    public function itBuildsFromArray()
    {
        $message = MessageBuilder::createBuilder()
            ->fromArray([
                'id' => 1,
                'message' => 'Some message'
            ])
            ->build();

        $this->assertBuildedMessage($message);
    }

    /**
     * @test
     */
    public function itGetsMultipleProps()
    {
        $properties =  MessageBuilder::createBuilder()
            ->fromArray([
                'id' => 1,
                'message' => 'Some message'
            ])
            ->toArray(['id', 'message'])
        ;

        $this->assertEquals([
            'id' => 1,
            'message' => 'Some message'
        ], $properties);
    }

    /**
     * @test
     */
    public function itGetsAndSetsViaPropertyAccess()
    {
        $builder = MessageBuilder::createBuilder();

        $builder->id = 1;
        $builder->message = 'Some message';

        $this->assertBuildedMessage($builder->build());

        $this->assertEquals(1, $builder->id);
        $this->assertEquals('Some message', $builder->message);
    }

    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function itPreventsSettingNonExistingViaPropertyAccess()
    {
        $builder = MessageBuilder::createBuilder();
        $builder->none = 'something';
    }

    /**
     * @test
     */
    public function itSupportsIsset()
    {
        $builder = MessageBuilder::createBuilder()
            ->fromArray([
                'id' => 1,
                'message' => 'Some message'
            ]);

        $this->assertTrue(isset($builder->id));
        $this->assertFalse(isset($builder->none));
    }

    /**
     * @test
     */
    public function itSupportsGettersAndSetters()
    {
        $builder = MessageBuilder::createBuilder();

        $builder
            ->setId(1)
            ->setMessage('Some message');

        $this->assertEquals(1, $builder->getId());
        $this->assertEquals('Some message', $builder->getMessage());
    }

    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\BadMethodCallException
     */
    public function itPreventsSettingNonExistingViaSetter()
    {
        $builder = MessageBuilder::createBuilder();
        $builder->setNone('something');
    }

    /**
     * @test
     */
    public function itSupportsArrayAccess()
    {
        $builder = MessageBuilder::createBuilder();

        $builder['id'] = 1;
        $builder['message'] = 'Some message';

        $this->assertEquals(1, $builder['id']);
        $this->assertEquals('Some message', $builder['message']);
        $this->assertTrue(isset($builder['id']));
        $this->assertFalse(isset($builder['none']));
    }

    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\BadMethodCallException
     */
    public function itPreventsUnset()
    {
        $builder = MessageBuilder::createBuilder();
        unset($builder['id']);
    }

    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public function itPreventsSettingNonExistingViaArrayAccess()
    {
        $builder = MessageBuilder::createBuilder();
        $builder['none'] = 'something';
    }

    /**
     * @test
     */
    public function itConsultsGetterAndSetterMethodsAlways()
    {
        $builder = MessageBuilder::createBuilder();

        $this->assertEquals('getter_invoked', $builder->getCount());
        $this->assertEquals('getter_invoked', $builder['count']);
        $this->assertEquals('getter_invoked', $builder->count);

        $reflectionMethod = new \ReflectionMethod(AbstractBuilder::class, '__doGet');
        $reflectionMethod->setAccessible(true);

        $builder->setCount(10);
        $this->assertEquals(20, $reflectionMethod->invoke($builder, 'count'));

        $builder['count'] = 20;
        $this->assertEquals(30, $reflectionMethod->invoke($builder, 'count'));

        $builder->count = 30;
        $this->assertEquals(40, $reflectionMethod->invoke($builder, 'count'));
    }



    private function assertBuildedMessage(Message $message)
    {
        $this->assertEquals(1, $message->getId());
        $this->assertEquals('Some message', $message->getMessage());
        $this->assertInstanceOf(\DateTime::class, $message->getTimestamp());
    }
}
