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

use RunOpenCode\AbstractBuilder\AbstractBuilder;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;
use RunOpenCode\AbstractBuilder\Ast\MetadataLoader;
use RunOpenCode\AbstractBuilder\Ast\Printer;
use RunOpenCode\AbstractBuilder\Command\Question\ClassChoice;
use RunOpenCode\AbstractBuilder\Command\Question\GetterMethodChoice;
use RunOpenCode\AbstractBuilder\Command\Question\MethodChoice;
use RunOpenCode\AbstractBuilder\Command\Question\SetterMethodChoice;
use RunOpenCode\AbstractBuilder\Command\Style\RunOpenCodeStyle;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;
use RunOpenCode\AbstractBuilder\Generator\BuilderClassFactory;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;
use RunOpenCode\AbstractBuilder\Utils\ClassUtils;
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
     * @var RunOpenCodeStyle
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
            ->addOption('all', '-a', InputOption::VALUE_NONE, 'Generate all methods by default.')
            ->addOption('rtd', '-r', InputOption::VALUE_NONE, 'Generate methods with return types declarations.')
            ->addOption('print', '-p', InputOption::VALUE_NONE, 'Only display code without creating/modifying class file.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->style = new RunOpenCodeStyle($input, $output);

        $this->style->displayLogo();

        $this->style->title('Generate builder class');

        try {
            /**
             * @var ClassChoice $subjectChoice
             */
            $subjectChoice = $this->getBuildingClass();
            $this->style->info(sprintf('Builder class for class "%s" will be generated.', $subjectChoice->getClass()->getName()));

            /**
             * @var ClassChoice $builderChoice
             */
            $builderChoice = $this->getBuilderClass($subjectChoice);
            $this->style->info(sprintf('Full qualified namespace for builder class is "%s".', $builderChoice->getClass()->getName()));
            $this->style->info(sprintf('Path to file where builder class will be saved is "%s".', $builderChoice->getFile()->getFilename()));
            $builderChoice->getClass()->isAutoloadable() ? $this->style->info('Existing builder class will be updated.') : $this->style->info('New builder class will be created.');

            /**
             * @var MethodChoice[] $methods
             */
            $methodChoices = $this->getMethodsToGenerate($subjectChoice, $builderChoice);
            $this->style->info('Methods to generate are:');
            $this->style->ul($methodChoices);

            $classFactory = new BuilderClassFactory($subjectChoice->getClass(), $builderChoice->getClass(), $this->input->getOption('rtd'));

            foreach ($methodChoices as $methodChoice) {

                if ($methodChoice instanceof GetterMethodChoice) {
                    $classFactory->addGetter($methodChoice->getMethodName(), $methodChoice->getParameter());
                    continue;
                }

                if ($methodChoice instanceof SetterMethodChoice) {
                    $classFactory->addSetter($methodChoice->getMethodName(), $methodChoice->getParameter());
                    continue;
                }

                throw new RuntimeException(sprintf('Expected instance of "%s" or "%s", got "%s".', GetterMethodChoice::class, SetterMethodChoice::class, get_class($methodChoice)));
            }

            $this->write($builderChoice->getFile());

            $this->style->success('Builder class successfully generated!');
            return 0;

        } catch (\Exception $e) {
            $this->style->error($e->getMessage());
            return -1;
        }
    }

    /**
     * Get class name for which skeleton should be built.
     *
     * @return ClassChoice
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
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

        $fileMetadata = MetadataLoader::create()->load($class);
        $classMetadata = null;

        if (class_exists($class)) {
            $classMetadata = $fileMetadata->getClass($class);
        }

        if (1 === count($fileMetadata->getClasses())) {
            $classMetadata = array_values($fileMetadata->getClasses())[0];
        }

        if (null === $classMetadata) {
            throw new RuntimeException(sprintf('It is not possible to extract single class metadata from "%s", found %s definition(s).', $class, count($fileMetadata->getClasses())));
        }

        if (!ClassUtils::isBuildable($classMetadata)) {
            throw new InvalidArgumentException(sprintf('Builder class can not be generated for "%s", class has to have constructor with some parameters.', $classMetadata->getName()));
        }

        return new ClassChoice($fileMetadata, $classMetadata);
    }

    /**
     * Get class name for builder class.
     *
     * @param ClassChoice $subjectChoice
     *
     * @return ClassChoice
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    private function getBuilderClass(ClassChoice $subjectChoice)
    {
        $class = $this->input->getArgument('builder');

        if (null === $class) {
            $default = sprintf('%sBuilder', $subjectChoice->getClass()->getShortName());
            $helper = $this->getHelper('question');
            $question = new Question(sprintf('Enter full qualified class name of your builder class (default: "%s"): ', $default), $default);

            $class = $helper->ask($this->input, $this->output, $question);
        }

        $classChoice = null;

        if (class_exists($class, true)) {
            $fileMetadata = MetadataLoader::create()->load($class);
            $classMetadata = $fileMetadata->getClass($class);
            $classChoice = new ClassChoice($fileMetadata, $classMetadata);

            if (null !== $this->input->getArgument('location')) {
                throw new InvalidArgumentException('Builder class already exists and its location can not be changed.');
            }
        }

        if (file_exists($class)) {
            $fileMetadata = MetadataLoader::create()->load($class);

            if (1 !== count($fileMetadata->getClasses())) {
                throw new RuntimeException(sprintf('It is not possible to extract single class metadata from "%s", found %s definition(s).', $class, count($fileMetadata->getClasses())));
            }

            $classMetadata = array_values($fileMetadata->getClasses())[0];
            $classChoice = new ClassChoice($fileMetadata, $classMetadata);
        }

        if (null === $classChoice) {
            $classChoice = $this->generateBuilder($subjectChoice, $class);
        }

        if (!ClassUtils::isBuilder($classChoice->getClass())) {
            throw new RuntimeException(sprintf('Builder class must implement either "%s" or "%s", none of those detected for "%s".', ReflectiveAbstractBuilder::class, AbstractBuilder::class, $classChoice->getClass()->getName()));
        }

        return $classChoice;
    }

    /**
     * Generate new builder class.
     *
     * @param ClassChoice $subjectChoice
     * @param string $builderClassName
     *
     * @return ClassChoice
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    private function generateBuilder(ClassChoice $subjectChoice, $builderClassName)
    {
        $location = $this->input->getArgument('location');

        if (null === $location) {
            $helper = $this->getHelper('question');
            $question = new Question('Enter path where you want to store a builder class: ', null);

            $location = $helper->ask($this->input, $this->output, $question);
        }

        $location = str_replace('\\', '/', ltrim($location, '/'));

        if (substr($location, -strlen($location)) !== '.php') {
            $location = $location.'/'.ClassUtils::getShortName($builderClassName).'.php';
        }

        $directory = realpath(dirname($location));

        if (!is_dir($directory)) {
            throw new RuntimeException(sprintf('Provided path to directory "%s" where builder class ought to be stored is not path to directory.', $directory));
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(sprintf('Directory on path "%s" is not writeable.', $directory));
        }

        if (is_file($location) && !is_writable($location)) {
            throw new RuntimeException(sprintf('Provided path to builder class "%s" is not writeable.', $location));
        }

        $fileMetadata = (new BuilderClassFactory($subjectChoice->getClass(), null, $this->input->getOption('rtd')))->initialize($location, $builderClassName);
        $classMetadata = array_values($fileMetadata->getClasses())[0];

        return new ClassChoice($fileMetadata, $classMetadata);
    }

    /**
     * Get methods which ought to be generated.
     *
     * @param ClassChoice $subjectChoice
     * @param ClassChoice $builderChoice
     *
     * @return MethodChoice[]
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    private function getMethodsToGenerate(ClassChoice $subjectChoice, ClassChoice $builderChoice)
    {
        $methods = [];

        $buildingClass = $subjectChoice->getClass();
        $builderClass = $builderChoice->getClass();

        $parameters = $buildingClass->getPublicMethod('__construct')->getParameters();

        foreach ($parameters as $parameter) {
            $getter = new GetterMethodChoice($parameter);
            $setter = new SetterMethodChoice($parameter);

            if (!$builderClass->hasPublicMethod($getter->getMethodName())) {
                $methods[] = $getter;
            }

            if (!$builderClass->hasPublicMethod($setter->getMethodName())) {
                $methods[] = $setter;
            }
        }

        if (0 === count($methods)) {
            throw new RuntimeException('There are no methods to generate.');
        }

        if (true !== $this->input->getOption('all')) {

            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                'Choose which methods you want to generate for your builder class (separate choices with coma, enter none for all choices):',
                $methods,
                implode(',', array_keys($methods))
            );

            $question->setMultiselect(true);

            $selected = $helper->ask($this->input, $this->output, $question);

            $methods = array_filter($methods, function(MethodChoice $choice) use ($selected) {
                return in_array((string) $choice, $selected, true);
            });
        }

        return $methods;
    }

    /**
     * Print or display builder class file
     *
     * @param FileMetadata $file
     */
    private function write(FileMetadata $file)
    {
        if ($this->input->getOption('print')) {

            $this->style->title('Generated code:');

            $lines = explode("\n", Printer::getInstance()->print($file));

            $counter = 0;

            foreach ($lines as $line) {
                $this->style->writeln(sprintf('%s: %s', ++$counter, $line));
            }

            return;
        }

        Printer::getInstance()->dump($file);
    }
}
