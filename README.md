# Abstracr builder

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/abstract-builder.svg)](https://packagist.org/packages/runopencode/abstract-builder)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/abstract-builder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/abstract-builder/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/abstract-builder/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/abstract-builder/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/abstract-builder/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/abstract-builder/build-status/master)
[![Build Status](https://travis-ci.org/RunOpenCode/abstract-builder.svg?branch=master)](https://travis-ci.org/RunOpenCode/abstract-builder)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/15e91ee6-47e2-46ef-bb6a-b013e36620a2/big.png)](https://insight.sensiolabs.com/projects/15e91ee6-47e2-46ef-bb6a-b013e36620a2)

If you intend to use builder pattern in your project 
[(see article on Wikipedia)](https://en.wikipedia.org/wiki/Builder_pattern)
you can use a prototype implementation of builder pattern from this library.

In order to do so, you have to create your own builder class, extend
`RunOpenCode\AbstractBuilder\AbstractBuilder` and implement methods
`configureParameters` and `getObjectFqcn`.

For those extra lazy developers, there is a
`RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder` as well, which
implements `configureParameters` method by introspecting constructor parameters
and default values.

**NOTE:** Builder class MUST NOT validate provided parameters. Class
which ought to be builded must do such validation, because construction
process of any instance of any class must be valid whether is executed
with or without builder implementation.

## Implementation example

Let's say that we have some class `Message` for which we have to provide
a builder class. Here is how to do so:

    <?php
    
    class Message
    {
        private $id;
        private $message;
        private $timestamp;
    
        public function __construct($id, $message, $timestamp)
        {
            $this->id = $id;
            $this->message = $message;
            $this->timestamp = $timestamp;
        }
    }
    
    final class MessageBuilder extends \RunOpenCode\AbstractBuilder\AbstractBuilder
    {
        /**
         * Get builded message.
         *
         * Alias to \RunOpenCode\AbstractBuilder\AbstractBuilder::build() method.
         *
         * @see \RunOpenCode\AbstractBuilder\AbstractBuilder::build()
         *
         * @return object
         */
        public function getMessage()
        {
            return $this->build();
        }
    
        /**
         * {@inheritdoc}
         */
        protected function configureParameters()
        {
            return array(
                'id' => null,
                'message' => null,
                'timestamp' => new \DateTime('now')
            );
        }
    
        /**
         * {@inheritdoc}
         */
        protected function getObjectFqcn()
        {
            return Message::class;
        }
    }
    
## Builder usage example

Start with creating a builder, you can use plain constructor:

    $builder = new MessageBuilder();

or you can use a static method for that:

    $builder = MessageBuilder::create();

You can get/set each individual configured builder property via:

**Property access:**

    $builder->id = 1;
    $id = $builder->id;

**Setter method:**

    $builder->setId(1);
    $id = $builder->getId();

**Array access:**

    $builder['id'] = 1;
    $id = $builder['id'];

**Multiple properties, via array:**

    $builder->fromArray([ 'id' => 1, 'message' => 'Some message' ]);
    $allProperties = $builder->toArray();
    $someProperties = $builder->toArray([ 'id', 'message' ]);

And finaly, you can build conrete object by calling `build()` method, or
invoking builder as method:

    $message = $builder->build();
    $message = $builder();

## Chaining (fluent interface)

Fluent interface is supported for setter methods, so you can chain them, example:

    $message = MessageBuilder::create()
                    ->setId(1)
                    ->setMessage('Some message')
                    ->setTimestamp(new \DateTime('now'))
                    ->build();



