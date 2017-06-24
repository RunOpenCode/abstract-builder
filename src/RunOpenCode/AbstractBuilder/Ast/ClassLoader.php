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
use PhpParser\NodeVisitor\NameResolver;
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

        $this->traverser->addVisitor(new NameResolver());
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

            $this->traverser->traverse($this->parser->parse(file_get_contents($filename)));
            $classes = $this->introspector->getClasses();

            if (0 === count($classes)) {
                throw new RuntimeException(sprintf('Class definition does not exist in "%s".', $filename));
            }

            if ($filename === $class && count($classes) > 1) {
                throw new RuntimeException(sprintf('There are more than one class definition in "%s" and it can not be determined which class to use.', $filename));
            }

            if (1 === count($classes)) {

                return new ClassMetadata(
                    $classes[0]['ast'],
                    $classes[0]['namespace'],
                    $classes[0]['class'],
                    $filename,
                    $classes[0]['final'],
                    $classes[0]['abstract'],
                    $classes[0]['methods'],
                    null !== $classes[0]['parent'] ? $this->load($classes[0]['parent']) : null
                );
            }

            $class = trim('\\', $class);

            foreach ($classes as $metadata) {

                if ($class === $metadata['fqcn']) {

                    return new ClassMetadata(
                        $metadata['ast'],
                        $metadata['namespace'],
                        $metadata['class'],
                        $filename,
                        $metadata['final'],
                        $metadata['abstract'],
                        $metadata['methods'],
                        null !== $metadata['parent'] ? $this->load($metadata['parent']) : null
                    );
                }
            }
        }

        throw new RuntimeException(sprintf('Unable to load class from "%s".', $class));
    }
}
