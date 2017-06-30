<?php

namespace RunOpenCode\AbstractBuilder\Ast\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ClassMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\MethodMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\TraitMetadata;
use RunOpenCode\AbstractBuilder\Ast\MetadataLoader;
use RunOpenCode\AbstractBuilder\Utils\MethodUtils;

/**
 * Class FileMetadataIntrospectionVisitor
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Visitor
 */
class FileMetadataIntrospectionVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $uses;

    /**
     * @var ClassMetadata[]
     */
    private $classes;

    /**
     * @var TraitMetadata[]
     */
    private $traits;

    /**
     * @var array
     */
    private $ast;

    /**
     * @var FileMetadata
     */
    private $metadata;

    /**
     * @var array
     */
    private $stack = [];

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritDoc}
     *
     * Cleans up internal state
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->metadata = null;
        $this->uses = [];
        $this->classes = [];
        $this->traits = [];
        $this->ast = $nodes;

        $this->stack = [
            'trait_use' => []
        ];
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Stmt\Class_ && !$node->isAnonymous()) {
            $this->stack['trait_use'] = [];
        }

        if ($node instanceof Stmt\Trait_) {
            $this->stack['trait_use'] = [];
        }

        if ($node instanceof Stmt\TraitUse) {
            $this->processTraitUse($node);
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_ && !$node->isAnonymous()) {
            $name = (string) $node->namespacedName;
            $this->classes[$name] = new ClassMetadata($name, $this->getClassParentMetadata($node), $this->stack['trait_use'], $node->isFinal(), $node->isAbstract(), $this->getMethodsMetadata($node), $node);
        }

        if ($node instanceof Stmt\Trait_) {
            $name = (string) $node->namespacedName;
            $this->traits[$name] = new TraitMetadata($name, $this->stack['trait_use'], $this->getMethodsMetadata($node), $node);
        }
    }

    public function afterTraverse(array $nodes)
    {
        $this->metadata = new FileMetadata($this->filename, $this->uses, $this->classes, $this->traits, $this->ast);
    }

    /**
     * @return FileMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Load class parent.
     *
     * @param Stmt\Class_ $class
     *
     * @return null|ClassMetadata
     */
    private function getClassParentMetadata(Stmt\Class_ $class)
    {
        if (null !== $class->extends) {

            $name = $class->extends->toString();

            /**
             * @var FileMetadata $filemetadata
             */
            $filemetadata = (new MetadataLoader())->load($name);

            return $filemetadata->getClass($name);
        }

        return null;
    }

    private function getTraitMetadata($name)
    {
        $filemetadata = (new MetadataLoader())->load($name);
        return $filemetadata->getTraits()[$name];
    }

    /**
     * Process class methods
     *
     * @param Stmt\ClassLike $classLike
     *
     * @return MethodMetadata[]
     */
    private function getMethodsMetadata(Stmt\ClassLike $classLike)
    {
        $methods = [];

        foreach ($classLike->stmts as $stmt) {

            if ($stmt instanceof Stmt\ClassMethod) {
                $methods[] = MethodMetadata::fromClassMethod($stmt);
            }
        }

        return $methods;
    }

    /**
     * Process trait use declaration and drop on working stack.
     *
     * @param Stmt\TraitUse $node
     */
    private function processTraitUse(Stmt\TraitUse $node)
    {
        $traitMetadata = $this->getTraitMetadata((string)$node->traits[0]);

        $methods = [];

        foreach ($traitMetadata->getMethods() as $method) {
            $methods[$method->getName()] = $method;
        }

        /**
         * @var Stmt\TraitUseAdaptation\Alias $adaptation
         */
        foreach ($node->adaptations as $adaptation) {
            /**
             * @var MethodMetadata $original
             */
            $original = $methods[$adaptation->method];

            $methods[$adaptation->newName] = new MethodMetadata(
                $adaptation->newName,
                $original->isAbstract(),
                $original->isFinal(),
                MethodUtils::getVisibility($adaptation->newModifier, $original->getVisibility()),
                $original->getReturnType(),
                $original->byReference(),
                $original->isStatic(),
                $original->getParameters(),
                $original->getAst()
            );

            unset($methods[$original->getName()]);
        }

        $this->stack['trait_use'][] = new TraitMetadata($traitMetadata->getName(), $traitMetadata->getTraits(), $methods, $traitMetadata->getAst());
    }
}

