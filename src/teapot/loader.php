<?php namespace Teapot;

use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class Loader
{
    const FILENAME = 'teapot.yml';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Commands
     */
    protected $commands;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Parser
     */
    private $yamlParser;


    /**
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->finder = new Finder;
        $this->filesystem = new Filesystem;
        $this->yamlParser = new Parser;
    }

    /**
     * Add avaliable commands
     */
    public function addCommands() {
        // print_r($this->commands);exit;
        foreach ((array) $this->commands as $command) {
            $class = __NAMESPACE__ . "\\Commands\\".$command;
            $this->app->add(new $class);
        }
    }

    /**
     * Run the symfony console.
     * @return mixed
     */
    public function run() {
        $this->resolveCommands();
        $this->addCommands();
        $this->app->run();
    }

    private function resolveCommands() {

        if (! $this->filesystem->exists($filename = teapot_path() . '/' . self::FILENAME)) {
            // @todo write to stdout
            if (! $this->filesystem->exists($filename = __DIR__ . '/stubs/' . self::FILENAME)) {
                throw new \Exception("No teapot.yml configuration file found.");
            }
        }

        $metadata = $this->parseYamlFile($filename);

        return $this->commands = $this->getAvaiableCommand($metadata);
    }

    private function parseYamlFile($filename) {

        try {
            $config = $this->yamlParser->parse(file_get_contents($filename));
        } catch (ParseException $e) {
            throw new \Exception("Parse Error");
        }

        return is_array($config) ? $config : array();
    }

    private function getAvaiableCommand($metadata = []) {
        $commands = [];

        $finder = new Finder();
        $directories = $finder->directories()
                              ->in(__DIR__ .'/commands')
                              ->ignoreDotFiles(true)
                              ->depth(0);
        if (isset($metadata['exclude']['folder'])) {
            $directories->exclude($metadata['exclude']['folder']);
        }

        foreach ($directories as $dir) {
            $finder = new Finder();
            $files = $finder->files()
                            ->ignoreDotFiles(true);

            //add exclue files
            if (isset($metadata['exclude']['files'])) {
                foreach ($metadata['exclude']['files'] as $file) {
                    $files->notName($file . '.php');
                }
            }

            $files->in($dir->getRealPath());
            foreach ($files as $file) {
                $commands[] = $dir->getRelativePathname() . '\\' . $file->getBasename('.php');
            }
        }
        
        return $commands;
    }
}