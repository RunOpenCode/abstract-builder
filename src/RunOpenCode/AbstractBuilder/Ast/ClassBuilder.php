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

use PhpParser\Builder\Class_;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;

class ClassBuilder
{
    /**
     * @var ClassMetadata
     */
    private $builderClass;

    /**
     * @var ClassMetadata
     */
    private $buildingClass;

    /**
     * @var BuilderFactory
     */
    private $factory;

    /**
     * @var Node
     */
    private $namespaceNode;

    /**
     * @var Class_
     */
    private $classNode;

    /**
     * @var Standard
     */
    private $printer;

    public function __construct(ClassMetadata $buildingClass, ClassMetadata $builderClass, array $methods)
    {
        $this->buildingClass = $buildingClass;
        $this->builderClass = $builderClass;
        $this->factory = new BuilderFactory();
        $this->printer = new Standard();

        $this->namespaceNode = $this->factory->namespace($this->builderClass->getNamespace());

        if (null !== $builderClass->getAst()) {
            $this->classNode = $this->builderClass->getAst();
            $this->namespaceNode->addStmt($this->classNode);
            return;
        }

        $this->initializeBuilderClass($methods);

        $this->namespaceNode->addStmt($this->classNode->getNode());
    }

    public function dump()
    {
        file_put_contents($this->builderClass->getFilename(), $this->display());
    }

    public function display()
    {
        return $this->printer->prettyPrintFile([$this->namespaceNode->getNode()]);
    }

    public static function create(ClassMetadata $buildingClass, ClassMetadata $builderClass, array $methods)
    {
        return new static($buildingClass, $builderClass, $methods);
    }

    private function initializeBuilderClass($methods)
    {
        $this->classNode = $this->factory
            ->class($this->builderClass->getClass())
            ->extend('\\'.ReflectiveAbstractBuilder::class)
            ->setDocComment(sprintf(
'/**
 * Class %s
 *
 * This class is implementation of builder pattern 
 * for class %s. 
 *  
 * This class is autogenerated by runopencode/abstract-builder library.
 *
 * @package %s
 *
 * @see %s
 * @see https://en.wikipedia.org/wiki/Builder_pattern
 */',
                    $this->builderClass->getClass(), $this->buildingClass->getFqcn(), $this->builderClass->getNamespace(), $this->buildingClass->getFqcn()));

        if ($this->buildingClass->isAbstract()) {
            $this->classNode->makeAbstract();
        }

        if ($this->buildingClass->isFinal()) {
            $this->classNode->makeFinal();
        }

        /**
         * @var MethodMetadata $method
         */
        foreach ($methods as $method) {

            $methodFactory = $this->factory
                ->method($method->getName())
                ->makePublic();

            if ($method->getReturnType()) {
                $methodFactory->setReturnType($method->getReturnType() instanceof ClassMetadata ? (string) $method->getReturnType() : $method->getReturnType());
            }

            foreach ($method->getParameters() as $parameter) {
                $parameterFactory = $this->factory
                    ->param($parameter->getName());

                if ($parameter->getType()) {
                    $parameterFactory->setTypeHint($parameter->getType());
                }

                $methodFactory->addParam($parameterFactory->getNode());
            }

            $this->classNode->addStmt($methodFactory->getNode());
        }

        $getObjectFqcnMethod = $this->factory->method('getObjectFqcn')
            ->makeProtected()
            ->addStmt(new Node\Stmt\Return_(new Node\Scalar\String_($this->buildingClass->getFqcn())))
            ->setDocComment(sprintf(
'
/**
 * {@inheritdoc}
 */'
            ));

        $this->classNode->addStmt($getObjectFqcnMethod->getNode());
    }
}