<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Command\Style;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RunOpenCodeStyle
 *
 * @package RunOpenCode\AbstractBuilder\Command\Style
 */
class RunOpenCodeStyle extends SymfonyStyle
{
    /**
     * Display information message
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->writeln(sprintf('[<fg=green>OK</>] %s', (string) $message));
    }

    /**
     * Display unordered list items
     *
     * @param array $items
     */
    public function ul(array $items, $indentation = 0, $symbol = '•')
    {
        $spaces = str_repeat(' ', $indentation);

        foreach ($items as $item) {

            if (is_array($item)) {
                $this->ul($item, ++$indentation, $symbol);
                continue;
            }

            $this->writeln(sprintf('%s  [<fg=green>%s</>] %s', $spaces,$symbol, (string) $item));
        }
    }

    /**
     * Display logo.
     *
     * @return void
     */
    public function displayLogo()
    {
        $resource = fopen(__DIR__ . '/../../../../../LOGO', 'rb');

        while (($line = fgets($resource)) !== false) {
            $this->write('<fg=green>'.$line.'</>');
        }

        fclose($resource);
    }
}
