<?php

namespace OtherCode\CLI;


/**
 * Command Interface
 * @author Unay Santisteban <davidu@softcom.com>
 * @package OtherCode\CLI
 */
interface CommandInterface
{
    /**
     * Display a description message
     * @return string
     */
    public function description();

    /**
     * Show help message
     * @return string
     */
    public function help();

    /**
     * Main code execution
     * @param mixed $payload
     * @return mixed
     */
    public function run($payload = null);

}