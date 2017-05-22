<?php

/**
 * Class Application
 * @author usantisteban <usantisteban@othercode.es>
 * @package OtherCode\CLI
 */
class Application extends \OtherCode\CLI\Command
{

    /**
     * Main entry point
     * @var string
     */
    protected $main = 'cmd';

    /**
     * Available commands
     * @var array
     */
    protected $command = array(
        'Hello' => 'Commands\HelloCommand'
    );

    /**
     * Display a description message
     * @return string
     */
    public function description()
    {
        return "Command Line Framework";
    }

}

