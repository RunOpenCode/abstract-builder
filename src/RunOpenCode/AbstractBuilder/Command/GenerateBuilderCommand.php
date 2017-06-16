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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class GenerateBuilderCommand
 *
 * @package RunOpenCode\AbstractBuilder\Command
 */
class GenerateBuilderCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $style;

    private $input;

    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('runopencode:generate:builder')
            ->setDescription('Generates builder class skeleton for provided class.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->style = new SymfonyStyle($input, $output);

        $this->displayLogo();

        $this->style->title('Generate builder class');

        if (false === ($class = $this->getClass())) {
            return 0;
        }

        $gettersAndSetters = $this->getGettersAndSetters(array_map(function(\ReflectionParameter $parameter) {
            return $parameter->getName();
        }, (new \ReflectionClass($class))->getConstructor()->getParameters()));

        if (false === ($builderClass = $this->getBuilderClassName(sprintf('%sBuilder', $class)))) {
            return 0;
        }

        if (false === ($location = $this->getBuilderClassLocation((new \ReflectionClass(class_exists($builderClass) ? $builderClass : $class))->getFileName()))) {
            return 0;
        }
    }

    /**
     * Get class name for which skeleton should be built.
     *
     * @return string|bool
     */
    private function getClass()
    {
        $helper = $this->getHelper('question');
        $question = new Question('Enter full qualified class name for which you want to generate builder class: ', null);

        $class = $helper->ask($this->input, $this->output, $question);

        if (!class_exists($class, true)) {
            $this->style->error(sprintf('Unable to autoload class "%s". Does this class exists? Can it be autoloaded?', $class));
            return false;
        }

        return ltrim(str_replace('\\\\', '\\', $class), '\\');
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
     * Get class name for builder class.
     *
     * @param string $suggest
     * @return bool|string
     */
    private function getBuilderClassName($suggest)
    {
        $helper = $this->getHelper('question');
        $question = new Question(sprintf('Enter full qualified class name of your builder class (default: "%s"): ', $suggest), $suggest);

        $class = trim($helper->ask($this->input, $this->output, $question));

        if (!$class) {
            $this->style->error('You have to provide builder class name.');
            return false;
        }

        $class = ltrim(str_replace('\\\\', '\\', $class), '\\');

        foreach (explode('\\', $class) as $part) {

            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $part)) {
                $this->style->error(sprintf('Provided builder class name "%s" is not valid PHP class name.', $class));
                return false;
            }
        }

        return $class;
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

    /**
     * Display logo.
     *
     * @return void
     */
    private function displayLogo()
    {
        $resource = fopen(__DIR__.'/../../../../LOGO', 'rb');

        while (($line = fgets($resource)) !== false) {
            $this->style->write('<fg=green>'.$line.'</>');
        }

        fclose($resource);
    }
}
