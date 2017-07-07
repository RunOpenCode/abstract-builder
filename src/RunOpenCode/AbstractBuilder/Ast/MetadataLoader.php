<?php

namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use RunOpenCode\AbstractBuilder\Ast\Visitor\FileMetadataIntrospectionVisitor;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;

class MetadataLoader
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
     * @var FileMetadataIntrospectionVisitor
     */
    private $introspector;

    private function __construct()
    {
        $this->parser = Parser::getInstance();
        $this->traverser = new NodeTraverser();

        $this->traverser->addVisitor(new NameResolver());
    }

    /**
     * Load file metadata
     *
     * @param string $arg
     *
     * @return Metadata\FileMetadata
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public function load($arg)
    {
        $filename = $arg;

        if (
            class_exists($arg, true)
            ||
            trait_exists($arg, true))
        {
            $filename = (new \ReflectionClass($arg))->getFileName();
        }

        if (null !== $this->introspector) {
            $this->traverser->removeVisitor($this->introspector);
        }

        $this->traverser->addVisitor($this->introspector = new FileMetadataIntrospectionVisitor($filename));

        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('Unable to load file metadata from "%s".', $arg));
        }

        $this->traverser->traverse($this->parser->parse(file_get_contents($filename)));

        return $this->introspector->getMetadata();
    }

    /**
     * MetadataLoader Factory
     *
     * @return MetadataLoader
     */
    public static function create()
    {
        return new static();
    }
}
