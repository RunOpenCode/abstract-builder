# Abstracr builder

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/abstract-builder.svg)](https://packagist.org/packages/runopencode/abstract-builder)

If you intend to use builder pattern in your project 
[(see article on Wikipedia)](https://en.wikipedia.org/wiki/Builder_pattern)
you can use a prototype implementation of builder pattern from this library.

In order to do so, you have to create your own builder class, extend
`RunOpenCode\AbstractBuilder\AbstractBuilder` and implement methods
`configureParameters` and `getObjectFqcn`.

**NOTE:** Builder class MUST NOT validate provided parameters. Class
which ought to be builded must do such validation, because construction
process of any instance of any class must be valid whether is executed
with or without builder implementation.

## Example

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
    
    



