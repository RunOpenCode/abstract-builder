<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Command;

use RunOpenCode\AbstractBuilder\Ast\ClassLoader;
use RunOpenCode\AbstractBuilder\Ast\ClassMetadata;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class GenerateBuilderCommand
 *
 * @package RunOpenCode\AbstractBuilder\Command
 */
class GenerateBuilderCommand extends Command
{
    /**
     * @var Style
     */
    private $style;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ClassLoader
     */
    private $loader;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('runopencode:generate:builder')
            ->setDescription('Generates builder class skeleton for provided class.')
            ->addArgument('class', InputArgument::OPTIONAL, 'Full qualified class name of building object that can be autoloaded, or path to file with class definition.')
            ->addArgument('builder', InputArgument::OPTIONAL, 'Full qualified class name of builder class can be autoloaded, or it will be autoloaded, or path to file with class definition.')
            ->addArgument('location', InputArgument::OPTIONAL, 'Path to location of file where builder class will be saved.')
            ->addOption('all', '-a', InputOption::VALUE_NONE, 'Should all methods be generated by default.');

        $this->loader = new ClassLoader();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->style = new Style($input, $output);

        $this->style->displayLogo();

        $this->style->title('Generate builder class');

        try {
            /**
             * @var ClassMetadata $buildingClass
             */
            $buildingClass = $this->getBuildingClass();
            $this->style->info(sprintf('Builder class for class "%s" will be generated.', $buildingClass->getFqcn()));

            /**
             * @var ClassMetadata $builderClass
             */
            $builderClass = $this->getBuilderClass($buildingClass);
            $this->style->info(sprintf('Full qualified namespace for builder class is "%s".', $builderClass->getFqcn()));
            $this->style->info(sprintf('Path to file where builder class will be saved is "%s".', $builderClass->getFilename()));
            class_exists($builderClass->isDefined()) ? $this->style->info('Existing builder class will be updated.') : $this->style->info('New builder class will be created.');

            $methods = $this->getMethods($buildingClass, $builderClass);
            $this->style->info(sprintf('Methods to generate are: "%s".', implode('", "', $methods)));

        } catch (\Exception $e) {
            $this->style->error($e->getMessage());
            return 0;
        }

    }

    /**
     * Get class name for which skeleton should be built.
     *
     * @return ClassMetadata
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    private function getBuildingClass()
    {
        $class = $this->input->getArgument('class');

        if (null === $class) {
            $helper = $this->getHelper('question');
            $question = new Question('Enter full qualified class name, or path to file with class, for which you want to generate builder class: ', null);

            $class = $helper->ask($this->input, $this->output, $question);
        }

        $metadata = $this->loader->load($class);

        if (null === ($constructor = $metadata->getConstructor())) {
            throw new InvalidArgumentException('Builder class can not be generated for class without constructor.');
        }

        if (0 === count($constructor->getParameters())) {
            throw new InvalidArgumentException('Builder class can not be generated for class with constructor without arguments.');
        }

        return $metadata;
    }

    /**
     * Get class name for builder class.
     *
     * @param ClassMetadata $buildingClass
     *
     * @return ClassMetadata
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    private function getBuilderClass(ClassMetadata $buildingClass)
    {
        $class = $this->input->getArgument('builder');

        if (null === $class) {
            $default = sprintf('%sBuilder', $buildingClass->getFqcn());
            $helper = $this->getHelper('question');
            $question = new Question(sprintf('Enter full qualified class name of your builder class (default: "%s"): ', $default), $default);

            $class = $helper->ask($this->input, $this->output, $question);
        }

        if (class_exists($class, true) || file_exists($class)) {
            return $this->loader->load($class);
        }

        return $this->getBuilderLocation(ClassMetadata::create($class));
    }

    /**
     * Get builder class location.
     *
     * @param ClassMetadata $builderClass
     *
     * @return ClassMetadata
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    private function getBuilderLocation(ClassMetadata $builderClass)
    {
        $location = $this->input->getArgument('location');

        if (null !== $location && $builderClass->isDefined() && $location !== $builderClass->getFilename()) {
            throw new InvalidArgumentException(sprintf('You can not provide new file location for existing builder ("%s" to "%s").', $builderClass->getFilename(), $location));
        }

        if ($builderClass->isDefined()) {
            return $builderClass;
        }

        if (null === $location) {
            $helper = $this->getHelper('question');
            $question = new Question('Enter path to directory where you want to store builder class: ', null);

            $path = str_replace('\\', '/', ltrim($helper->ask($this->input, $this->output, $question), '/'));

            if (!is_dir($path)) {
                throw new RuntimeException(sprintf('Provided path "%s" is not path to directory.', $path));
            }

            if (!is_writable($path)) {
                throw new RuntimeException(sprintf('Directory on path "%s" is not writeable.', $path));
            }

            $location = $path.'/'.end(explode('/', $builderClass->getClass())).'.php';
        }

        return ClassMetadata::clone($builderClass, [ 'filename' => $location ]);
    }

    /**
     * Get methods which ought to be generated.
     *
     * @param ClassMetadata $buildingClass
     * @param ClassMetadata $builderClass
     *
     * @return array
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    private function getMethods(ClassMetadata $buildingClass, ClassMetadata $builderClass)
    {
        $methods = [];

        foreach ($constructorParameters as $parameter) {
            $getter = sprintf('get%s', ucfirst($parameter->getName()));
            $setter = sprintf('set%s', ucfirst($parameter->getName()));

            if (!in_array($getter, $builderMethods, true)) {
                $methods[] = sprintf('%s()', $getter);
            }

            if (!in_array($setter, $builderMethods, true)) {
                $type = (null !== $parameter->getType()) ? '\\'.$parameter->getType().' ' : '';
                $methods[] = sprintf('%s(%s$%s)', $setter, $type, $parameter->getName());
            }
        }

        if (0 === count($methods)) {
            return [];
        }

        if (true !== $this->input->getOption('all')) {

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Choose which methods you want to generate for your builder class (separate choices with coma, enter none for all choices):',
                $methods,
                implode(',', array_keys($methods))
            );
            $question->setMultiselect(true);

            $methods = $helper->ask($this->input, $this->output, $question);
        }

        if (0 === count($methods)) {
            throw new RuntimeException('You have to choose at least one method to generate.');
        }

        return $methods;
    }
}
