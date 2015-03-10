<?php namespace Teapot\Commands\Core;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command {

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('init')
                  ->setDescription('Create a stub teapot.yml file');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_dir(teapot_path()))
        {
            throw new \InvalidArgumentException("Teapot has already been initialized.");
        }

        mkdir(teapot_path());

        copy(__DIR__.'/../../stubs/teapot.yml', teapot_path().'/teapot.yml');

        $output->writeln('<comment>Creating teapot.yml file...</comment> <info>✔</info>');
        $output->writeln('<comment>teapod.yml file created at:</comment> '.teapot_path().'/teapot.yml');
    }

}
