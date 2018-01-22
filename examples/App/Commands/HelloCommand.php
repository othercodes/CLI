<?php

namespace Sample\App\Commands;

/**
 * Class HelloCommand
 * @package Sample\App\Commands
 */
class HelloCommand extends \OtherCode\CLI\Command
{
    /**
     * Display a description message
     * @return string
     */
    public function description()
    {
        return "Say hello world!";
    }

    /**
     * Main code
     * @param null $payload
     * @return mixed|void
     */
    public function run($payload = null)
    {
        $this->writter->debug('Hello World Debug');
        $this->writter->info('Hello World Info');
        $this->writter->notice('Hello World Notice');
        $this->writter->warning('Hello World Warning');
        $this->writter->error('Hello World Error');
        $this->writter->critical('Hello World Critical');
        $this->writter->alert('Hello World Alert');
        $this->writter->emergency('Hello World Emergency');
        $this->writter->success('Hello World Success');

        $x = $this->writter->input("do yo like it?", 'n', true);

        $this->writter->info("Your response is {response}", array(
            'response' => $x
        ));
    }
}