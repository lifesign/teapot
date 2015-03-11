<?php namespace Teapot\Commands\Project;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Teapot\Loader;
use ZipArchive;

class ProjectInitCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('project:init')
             ->setDescription('Create a new Fresh Laravel Project.')
             ->addArgument('name',
                InputArgument::REQUIRED,
                'Your application name.')
             ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'The laravel version you want to install.');

    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->verifyApplicationDoesntExist(
            $directory = getcwd().'/'.$input->getArgument('name'),
            $output
        );

        $version = $input->getArgument('version') ?: 'latest';

        $output->writeln("<info>Crafting application...</info>");
        $output->writeln("<info>Target Directory is {$directory}.</info>");
        $output->writeln("<info>Download Laravel Version is {$version}</info>");

        if ($this->resolveVersion($version, $directory, $input, $output)) {
            // run some create project composer command
            $composer = $this->findComposer();
            $commands = array(
                $composer.' run-script post-install-cmd',
                $composer.' run-script post-create-project-cmd',
            );

            $process = new Process(implode(' && ', $commands), $directory, null, null, null);

            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });

            $output->writeln('<comment>Application ready! Build something amazing.</comment>');

        } else {
            $output->writeln('<error>Unknown version (Support: `v5`, `v4` or `latest`)</error>');
        }
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory, OutputInterface $output)
    {
        if (is_dir($directory)) {
            $output->writeln('<error>Application already exists!</error>');

            exit(1);
        }
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd().'/laravel_'.md5(time().uniqid()).'.zip';
    }

    /**
     * Resolve the download version
     *
     * @param                  $version
     * @param                  $directory
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return $this|bool
     */
    protected function resolveVersion($version, $directory, InputInterface $input, OutputInterface $output)
    {
        //support v4,v5,latest
        if ('v4' == $version ||
            'v5' == $version ||
            'latest' == $version) {
            $this->downloadVersion($version, $directory);

            return $this;
        }

        return false;
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function download($url, $zipFile)
    {
        $response = \GuzzleHttp\get($url)->getBody();

        file_put_contents($zipFile, $response);

        return $this;
    }

    /**
     * Download the specified version zip
     * @param  string $version
     * @param  string $directory
     * @return $this
     */
    protected function downloadVersion($version, $directory)
    {
        $config = with(new Parser)->parse(file_get_contents(teapot_path() . '/'.Loader::FILENAME));
        $url = $config['url'][$version];

        $this->download($url, $zipFile = $this->makeFilename())
             ->extract($zipFile, $directory)
             ->cleanUp($zipFile);
    }


    /**
     * Extract the zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $archive->open($zipFile);

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }
        return 'composer';
    }
}

