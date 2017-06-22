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
use RunOpenCode\AbstractBuilder\Exception\NotSupportedException;

/**
 * Class ClassIntrospectionVisitor
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Visitor
 */
class ClassIntrospectionVisitor extends NodeVisitorAbstract
{
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
     * {@inheritDoc}
     *
     * Cleans up internal state
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = null;
        $this->class = null;
        $this->final = false;
        $this->abstract = false;
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

        if ($node instanceof Stmt\Class_) {

            if (null !== $this->class) {
                throw new NotSupportedException(sprintf('Multiple namespaces in single file are not supported.'));
            }

            $this->class = $node->name;
            $this->final = $node->isFinal();
            $this->abstract = $node->isAbstract();
        }

        if ($node instanceof Stmt\Property) {

        }
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * @return bool
     */
    public function isAbstract()
    {
        return $this->abstract;
    }
}
