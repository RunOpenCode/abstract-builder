<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use RunOpenCode\AbstractBuilder\Ast\Visitor\ClassIntrospectionVisitor;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;

class ClassLoader
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var \PhpParser\NodeTraverser
     */
    private $traverser;

    /**
     * @var ClassIntrospectionVisitor
     */
    private $introspector;

    /**
     * ClassLoader constructor.
     */
    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser();
        $this->introspector = new ClassIntrospectionVisitor();

        $this->traverser->addVisitor($this->introspector);
    }

    /**
     * Load class and build its metadata value object
     *
     * @param string $class Filename or full qualified class name
     *
     * @return ClassMetadata
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function load($class)
    {
        $filename = $class;

        if (class_exists($class, true)) {
            $filename = (new \ReflectionClass($class))->getFileName();
        }

        if (file_exists($filename)) {

            $ast = $this->traverser->traverse($this->parser->parse(file_get_contents($filename)));

            return new ClassMetadata(
                $ast,
                $this->introspector->getNamespace(),
                $this->introspector->getClass(),
                $filename,
                $this->introspector->isFinal(),
                $this->introspector->isAbstract()
            );
        }

        throw new RuntimeException(sprintf('Unable to load class from "%s".', $class));
    }
}
