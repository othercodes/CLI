<?php

namespace Sample\App;


/**
 * Class CLIApplication
 * @package Sample\App
 */
class CLIApplication extends \OtherCode\CLI\Command
{

    /**
     * Command name
     */
    const NAME = 'SampleCLIApplication';

    /**
     * Current version
     */
    const VERSION = "1.0.0";

    /**
     * Available commands
     * @var array
     */
    protected $commands = array(
        'hello' => 'Sample\App\Commands\HelloCommand',
    );

    /**
     * Display a description message
     * @return string
     */
    public function description()
    {
        return "Sample CLI Application.";
    }

}