<?php namespace Teapot;

use Symfony\Component\Console\Application;

class Loader
{
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Add avaliable commands
     */
    public function loadCommands() {
        $this->app->add(new Commands\Project\ProjectInitCommand());
        /**/
    }

    /**
     * Run the symfony console.
     * @return mixed
     */
    public function run() {
        $this->loadCommands();
        $this->app->run();
    }
}