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

use RunOpenCode\AbstractBuilder\Exception\RuntimeException;
use RunOpenCode\AbstractBuilder\Helper\Style;
use RunOpenCode\AbstractBuilder\Helper\Tokenizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('runopencode:generate:builder')
            ->setDescription('Generates builder class skeleton for provided class.')
            ->addArgument('class', InputArgument::OPTIONAL, 'Full qualified class name of building object that can be autoloaded, or path to file with class definition.')
            ->addArgument('builder', InputArgument::OPTIONAL, 'Full qualified class name of builder class can be autoloaded, or it will be autoloaded, or path to file with class definition.')
            ->addArgument('location', InputArgument::OPTIONAL, 'Path to location of file where builder class will be saved.');
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
            $buildingClass = $this->getBuildingClass();
            $this->style->info(sprintf('Builder class for class "%s" will be generated.', $buildingClass));

            $builderClass = $this->getBuilderClass(sprintf('%sBuilder', $buildingClass));
            $this->style->info(sprintf('Full qualified namespace for builder class is "%s".', $builderClass));
        } catch (\Exception $e) {
            $this->style->error($e->getMessage());
            return 0;
        }

    }

    /**
     * Get class name for which skeleton should be built.
     *
     * @return string|bool
     */
    private function getBuildingClass()
    {
        $class = $this->input->getArgument('class');

        if (null === $class) {
            $helper = $this->getHelper('question');
            $question = new Question('Enter full qualified class name, or path to file with class, for which you want to generate builder class: ', null);

            $class = $helper->ask($this->input, $this->output, $question);
        }

        if (!class_exists($class, true)) {
            $class = Tokenizer::findClass($class);
        }

        if (!class_exists($class, true)) {
            throw new RuntimeException(sprintf('Unable to autoload class "%s". Does this class exists? Can it be autoloaded?', $class));
        }

        return ltrim(str_replace('\\\\', '\\', $class), '\\');
    }

    /**
     * Get class name for builder class.
     *
     * @param string $suggest
     * @return bool|string
     */
    private function getBuilderClass($suggest)
    {
        $class = $this->input->getArgument('builder');

        if (null === $class) {
            $helper = $this->getHelper('question');
            $question = new Question(sprintf('Enter full qualified class name of your builder class (default: "%s"): ', $suggest), $suggest);

            $class = $helper->ask($this->input, $this->output, $question);
        }

        if (file_exists($class) && !class_exists($class, true)) {
            $class = Tokenizer::findClass($class);
        }

        $class = ltrim(str_replace('\\\\', '\\', $class), '\\');

        if ('' === $class) {
            throw new RuntimeException('Builder class name must be provided.');
        }

        foreach (explode('\\', $class) as $part) {

            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $part)) {
                throw new RuntimeException(sprintf('Provided builder class name "%s" is not valid PHP class name.', $class));
            }
        }

        return $class;
    }

    /**
     * Get getters and setters that will be generated.
     *
     * @param string[] $parameters
     * @return string[]
     */
    private function getGettersAndSetters($parameters)
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Choose for which constructor arguments you want to generate getters and setters (separate choices with coma, enter none for all choices):',
            $parameters,
            implode(',', array_keys($parameters))
        );

        $question->setMultiselect(true);

        $selected = $helper->ask($this->input, $this->output, $question);

        if (0 === count($selected)) {
            $this->style->error('You have to choose at least one constructor argument.');
        }

        return
            array_merge(
                array_map(function($parameter) {
                    return sprintf('get%s()', ucfirst($parameter));
                }, $selected),
                array_map(function($parameter) {
                    return sprintf('set%s($%s)', ucfirst($parameter), $parameter);
                }, $selected)
            );
    }


    /**
     * Get builder class location
     *
     * @param string $suggest
     * @return bool|string
     */
    private function getBuilderClassLocation($suggest)
    {
        $helper = $this->getHelper('question');
        $question = new Question(sprintf('Where do you want to generate your builder class code (default: "%s"): ', $suggest), $suggest);

        $location = trim($helper->ask($this->input, $this->output, $question));

        if (file_exists($location) && !is_writable($location)) {
            $this->style->error(sprintf('File on location "%s" already exists, but it is not writeable.', $location));
            return false;
        }

        // TODO - check if it si possible to create file at all...?

        return $location;
    }
}
