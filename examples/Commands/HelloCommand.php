<?php

namespace Commands;

/**
 * HelloCommand
 * @author uantisteban <usantisteban@othercode.es>
 * @package CommandLineFramework
 */
class HelloCommand extends \OtherCode\CLI\Command
{

    protected $options = array(
        '-f|feeback' => 'Demo option.'
    );

    /**
     * Display a description message
     * @return string
     */
    public function description()
    {
        return "Hello Command";
    }

    /**
     * Main code execution
     * @param mixed $payload
     * @return mixed
     */
    public function run($payload = null)
    {
        $this->write('Hello!');

        if (isset($this->arguments['feeback'])) {

            $name = $this->input('What\'s your name?', null, true);

            $this->write('How are you, {name}?', array(
                '{name}' => $name,
            ));
        }
    }
}