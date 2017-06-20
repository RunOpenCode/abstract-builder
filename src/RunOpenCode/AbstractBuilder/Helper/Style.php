<?php

namespace RunOpenCode\AbstractBuilder\Helper;

use Symfony\Component\Console\Style\SymfonyStyle;

class Style extends SymfonyStyle
{
    /**
     * Display information message
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->writeln(sprintf('<fg=green>[OK]</> %s', $message));
    }

    /**
     * Display logo.
     *
     * @return void
     */
    public function displayLogo()
    {
        $resource = fopen(__DIR__.'/../../../../LOGO', 'rb');

        while (($line = fgets($resource)) !== false) {
            $this->write('<fg=green>'.$line.'</>');
        }

        fclose($resource);
    }
}
