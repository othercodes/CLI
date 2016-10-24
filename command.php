#!/usr/bin/php
<?php

namespace OtherCode\Commands;

/**
 * Example of command script in php.
 *
 * 0.1-beta 
 */
class Script
{
   /**
    * Script version
    * @var string
    */
    const VERSION = '1.0.0';

    /**
     * List of available params
     * @var array
     */
    private $parameters = array(
        '-a' => 'param_one',
        '-b' => 'param_two',
    );

    /**
     * Logs storage
     * @var array
     */
    private $logs = array();

    /**
     * Script constructor.
     * @param array $argv
     * @throws \Exception
     */
    public function __construct($argv)
    {
        foreach ($this->parameters as $key => $param) {
            $search = array_search($key, $argv);
            if ($search !== false && isset($argv[$search + 1])) {
                $this->{$param} = $argv[$search + 1];
            }
        }
    }

    private function check()
    { 
    
    }

    /**
     * Main logic of the script, here is the magic!!
     */
    private function mainLogic()
    {

    }

    /**
     * Write a text line.
     * @param string $text
     * @param bool $newline
     */
    private function write($text, $newline = true)
    {
        echo((string)$text . (($newline) ? "\n" : ""));
    }

    /**
     * Save a log
     * @param $message
     */
    private function log($message)
    {
        $this->logs[] = date('Y-m-d H:i:s') . ' ' . $message;
    }

    /**
     * Run the actual logic
     */
    public function __destruct()
    {
        try {

            /**
             * Execute a pre-check of the classes and
             * files I have to use to work properly
             */
            $this->check();

            /**
             * run the main logic
             */
            $this->mainLogic();

        } catch (\Exception $e) {
            $this->write('Error ' . $e->getMessage());
        }

        /**
         * Final log dump
         */
        @file_put_contents($this->output, implode($this->logs, "\n") . "\n", FILE_APPEND);

    }
}

new Script($argv);
