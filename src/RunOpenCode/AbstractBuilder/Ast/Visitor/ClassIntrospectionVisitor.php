<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Ast\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;
use RunOpenCode\AbstractBuilder\Ast\MethodMetadata;
use RunOpenCode\AbstractBuilder\Exception\NotSupportedException;

/**
 * Class ClassIntrospectionVisitor
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Visitor
 */
class ClassIntrospectionVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $classes;

    /**
     * @var array
     */
    private $ast;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $final;

    /**
     * @var bool
     */
    private $abstract;

    /**
     * @var MethodMetadata[]
     */
    private $methods;

    /**
     * @var string|null
     */
    private $parent;

    /**
     * {@inheritDoc}
     *
     * Cleans up internal state
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->classes = [];

        $this->ast = null;
        $this->namespace = null;
        $this->class = null;
        $this->final = false;
        $this->abstract = false;
        $this->methods = [];
        $this->parent = null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\NotSupportedException
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Stmt\Namespace_) {

            if (null !== $this->namespace) {
                throw new NotSupportedException(sprintf('Multiple namespaces in single file are not supported.'));
            }

            $this->namespace = $node->name->toString();
        }

        if ($node instanceof Stmt\Class_ && !$node->isAnonymous()) {

            if (null !== $this->class) {
                throw new NotSupportedException(sprintf('Multiple classes in single file are not supported.'));
            }

            $this->ast = $node;
            $this->class = $node->name;
            $this->final = $node->isFinal();
            $this->abstract = $node->isAbstract();

            if (null !== $node->extends) {
                $this->parent = $node->extends->toString();
            }
        }

        if (null !== $this->class && $node instanceof Stmt\ClassMethod) {
            $this->methods[] = MethodMetadata::fromClassMethod($node);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_) {

            $this->classes[] = [
                'ast' => $this->ast,
                'namespace' => $this->namespace,
                'class' => $this->class,
                'fqcn' => $this->namespace.'\\'.$this->class,
                'final' => $this->final,
                'abstract' => $this->abstract,
                'methods' => $this->methods,
                'parent' => $this->parent
            ];

            $this->class = null;
            $this->final = false;
            $this->abstract = false;
            $this->methods = [];
            $this->parent = null;
        }
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }
}
