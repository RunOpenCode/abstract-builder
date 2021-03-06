<?php

namespace RunOpenCode\AbstractBuilder\Generator;

use PhpParser\Comment;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use RunOpenCode\AbstractBuilder\Ast\BuilderFactory;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ClassMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ParameterMetadata;
use RunOpenCode\AbstractBuilder\Ast\MetadataLoader;
use RunOpenCode\AbstractBuilder\Ast\Parser;
use RunOpenCode\AbstractBuilder\Exception\LogicException;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;
use RunOpenCode\AbstractBuilder\Utils\ClassUtils;

/**
 * Class BuilderClassFactory
 *
 * @package RunOpenCode\AbstractBuilder\Generator
 */
class BuilderClassFactory
{
    /**
     * @var BuilderFactory
     */
    private $factory;

    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var ClassMetadata
     */
    private $subject;

    /**
     * @var ClassMetadata
     */
    private $builder = null;

    /**
     * @var bool
     */
    private $withReturnTypeDeclaration;

    public function __construct(ClassMetadata $subject, ClassMetadata $builder = null, $withReturnTypeDeclaration = false)
    {
        $this->factory = BuilderFactory::getInstance();
        $this->parser = Parser::getInstance();
        $this->builder = $builder;
        $this->subject = $subject;
        $this->withReturnTypeDeclaration = $withReturnTypeDeclaration;
    }

    public function addBuildMethod()
    {
        if ($this->builder->hasPublicMethod('build', false)) {
            throw new LogicException(sprintf('Method "build()" is already defined in "%s".', $this->builder->getName()));
        }

        $method = $this->factory->method('build')
            ->makePublic()
            ->addStmts($this->parser->parse('<?php return parent::build();'))
            ->setDocComment(sprintf(
                '
/**
 * Builds new instance of %s from provided arguments. 
 *
 * @return %s
 */', $this->subject->getFqcn(), $this->subject->getFqcn()));

        if ($this->withReturnTypeDeclaration) {
            $method->setReturnType($this->subject->getFqcn());
        }

        $this->appendMethod($method->getNode());

        return $this;
    }

    public function addCreateBuilderMethod()
    {
        if ($this->builder->hasPublicMethod('createBuilder', false)) {
            throw new LogicException(sprintf('Method "createBuilder()" is already defined in "%s".', $this->builder->getFqcn()));
        }

        if ($this->builder->isAbstract()) {
            throw new LogicException(sprintf('Method "createBuilder()" can not be generated for class "%s" since it\'s abstract.', $this->builder->getFqcn()));
        }

        $method = $this->factory->method('createBuilder')
            ->makePublic()
            ->makeStatic()
            ->addStmts($this->parser->parse('<?php return parent::createBuilder();'))
            ->setDocComment(sprintf(
                '
/**
 * Produces new builder.
 *
 * @return %s
 *
 * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
 */', $this->builder->getFqcn()));

        if ($this->withReturnTypeDeclaration) {
            $method->setReturnType($this->builder->getFqcn());
        }

        $this->appendMethod($method->getNode());

        return $this;
    }

    public function addGetObjectFqcnMethod()
    {
        if ($this->builder->hasPublicMethod('getObjectFqcn', false)) {
            throw new LogicException(sprintf('Method "getObjectFqcn()" is already defined in "%s".', $this->builder->getFqcn()));
        }

        $method = $this->factory->method('getObjectFqcn')
            ->makeProtected()
            ->addStmts($this->parser->parse(sprintf('<?php return %s::class;', $this->subject->getFqcn())))
            ->setDocComment(
                '
/**
 * {@inheritdoc}
 */'
            );

        if ($this->withReturnTypeDeclaration) {
            $method->setReturnType('string');
        }

        $this->appendMethod($method->getNode());

        return $this;
    }

    public function addConfigureParametersMethod()
    {
        if ($this->builder->hasPublicMethod('configureParameters', false)) {
            throw new LogicException(sprintf('Method "configureParameters()" is already defined in "%s".', $this->builder->getFqcn()));
        }

        $method = $this->factory->method('configureParameters')
            ->makeProtected()
            ->addStmts($this->parser->parse("<?php \$defaults = parent::configureParameters();\n// Modify default values here\nreturn \$defaults;"))
            ->setDocComment(
                '
/**
 * You can override default building parameter values here 
 *
 * {@inheritdoc}
 */'
            );

        if ($this->withReturnTypeDeclaration) {
            $method->setReturnType('array');
        }

        $this->appendMethod($method->getNode());

        return $this;
    }

    public function addGetter($name, ParameterMetadata $parameter)
    {
        $method = $this->factory->method($name)
            ->makePublic()
            ->addStmts($this->parser->parse(sprintf('<?php return $this->__doGet(\'%s\');', $parameter->getName())))
            ->setDocComment(sprintf(
                '
/**
 * Get value for constructor parameter %s 
 *
 * @return %s
 */', $parameter->getName(), $parameter->getType() ?: 'mixed'));

        if ($this->withReturnTypeDeclaration && $parameter->getAst()->type) {
            $method->setReturnType($parameter->getAst()->type);
        }

        $this->builder->getAst()->stmts[] = $method->getNode();

        return $this;
    }

    public function addSetter($name, ParameterMetadata $parameter)
    {
        $ast = $parameter->getAst();

        $method = $this->factory->method($name)
            ->makePublic()
            ->addParam(new Param(
                $ast->name,
                null,
                $ast->type,
                $ast->byRef,
                $ast->variadic,
                $ast->getAttributes()
            ))
            ->addStmts($this->parser->parse(sprintf('<?php return $this->__doSet(\'%s\', $%s);', $parameter->getName(), $parameter->getName())))
            ->setDocComment(sprintf(
                '
/**
 * Set value for constructor parameter %s 
 *
 * @return %s
 */', $parameter->getName(), $this->builder->getFqcn()));

        if ($this->withReturnTypeDeclaration) {
            $method->setReturnType($this->builder->getFqcn());
        }

        $this->appendMethod($method->getNode());

        return $this;
    }

    /**
     * Initializes new builder class
     *
     * @param string $filename
     * @param string $class
     *
     * @return FileMetadata
     */
    public function initialize($filename, $class)
    {
        $namespace = $this->factory->namespace($ns = ClassUtils::getNamespace($class));

        $builderAstFactory = $this->factory->class(ClassUtils::getShortName($class))
            ->extend('\\'.ReflectiveAbstractBuilder::class)
            ->setDocComment(sprintf(
                '
/**
 * Class %s
 *
 * This class is implementation of builder pattern 
 * for class %s. 
 *
 * @package %s
 *
 * @see \%s
 * @see https://en.wikipedia.org/wiki/Builder_pattern
 */', $class, $this->subject->getFqcn(), $ns, ReflectiveAbstractBuilder::class));


        if ($this->subject->isFinal()) {
            $builderAstFactory->makeFinal();
        }

        if ($this->subject->isAbstract()) {
            $builderAstFactory->makeAbstract();
        }

        $this->builder = new ClassMetadata(
            $class,
            MetadataLoader::create()->load(ReflectiveAbstractBuilder::class)->getClass(ReflectiveAbstractBuilder::class),
            [],
            $this->subject->isFinal(),
            $this->subject->isAbstract(),
            [],
            $builderAstFactory->getNode()
        );

        $namespace->addStmt($this->builder->getAst());

        $this
            ->addBuildMethod()
            ->addGetObjectFqcnMethod()
            ->addConfigureParametersMethod();

        if (!$this->builder->isAbstract()) {
            $this->addCreateBuilderMethod();
        }

        return new FileMetadata($filename, [], [$this->builder], [], [$namespace->getNode()]);
    }

    /**
     * Append method to class AST, order by visibility.
     *
     * @param ClassMethod $method
     */
    private function appendMethod(ClassMethod $method)
    {
        $this->builder->getAst()->stmts[] = $method;

        // TODO Sort according to visibility and other parameters.
    }
}

