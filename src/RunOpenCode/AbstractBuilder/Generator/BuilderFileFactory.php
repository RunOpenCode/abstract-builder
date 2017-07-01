<?php

namespace RunOpenCode\AbstractBuilder\Generator;

use RunOpenCode\AbstractBuilder\Ast\BuilderFactory;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;

class BuilderFileFactory
{
    /**
     * @var BuilderFactory
     */
    private $factory;

    public function __construct()
    {
        $this->factory = BuilderFactory::getInstance();
    }

    public function initialize($filename, $namespace)
    {
        $namespace = $this->factory->namespace($namespace);

        $namespace->addStmt($this->factory->use(ReflectiveAbstractBuilder::class));

        return new FileMetadata($filename, [], [], [], []);
    }
}
