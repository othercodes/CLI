<?php

namespace OtherCode\CLI;


/**
 * Class Command
 * @author Unay Santisteban <davidu@softcom.com>
 * @package OtherCode\CLI
 */
abstract class Command implements \OtherCode\CLI\CommandInterface
{
    /**
     * Current version
     */
    const VERSION = '1.0';

    /**
     * Application name
     */
    const NAME = 'CLI';

    /**
     * Name for the legal entry point
     * @var string
     */
    protected $main;

    /**
     * @var array
     */
    protected $command = array();

    /**
     * List of available arguments
     * @var array
     */
    protected $arguments = array();

    /**
     * List of available params
     * @var array
     */
    protected $options = array();

    /**
     * Command constructor.
     * @param array $argv
     * @throws \Exception
     */
    public function __construct(array $argv = array())
    {

        try {

            if (!ini_get('date.timezone')) {
                ini_set('date.timezone', 'UTC');
            }

            if (isset($this->main)) {
                $stub = array_shift($argv);

                if ($stub !== $this->main) {
                    $this->write("> Illegal entry point: " . $stub);

                    exit("> Shutting down CLI system.\n");
                }
            }

            /**
             * search in the command folder for all the available
             * files/classes and add them to the arguments list.
             */
            foreach ($this->command as $name => $class) {
                $this->arguments[strtolower($name) . '|' . $name] = $name;
            }

            /**
             * Append the default arguments to the arguments list
             *  -h help Show help information.
             */
            $this->arguments = array_merge($this->arguments, array(
                '-h|help' => 'Show help information.',
            ));

            /**
             * pre-process the arguments to allow a better search
             * in the argument string cli.
             */
            $options = array();
            foreach ($this->arguments as $arg => $description) {
                list($key, $param) = explode('|', $arg, 2);
                $options[trim($key, ":0123456789")] = array(
                    'description' => $description,
                    'param' => $param,
                    'key' => $key
                );
            }

            /**
             * process each element of the cli string finding
             * arguments, commands and arguments
             */
            $processed = array();
            foreach ($argv as $index => $arg) {
                $arg = trim(strtolower($arg));

                /**
                 * search the current element of the cli string in the
                 * registered key words (arguments), if it exists we
                 * get the "type" of the keyword if it have - it is an
                 * option if not try to find a command that match, finally
                 * if the element is'nt a command or option, we assume is a
                 * common input argument.
                 */
                if (array_key_exists($arg, $options) && !in_array($arg, $processed)) {
                    if (strpos($options[$arg]['key'], '-') === false) {

                        /**
                         * Search for a command for the current element of the
                         * cli string if it exists we instantiate it and delegate
                         * the rest of the cli string.
                         */
                        $fqn = '\Commands\\' . trim($options[$arg]['param']) . 'Command';
                        if (class_exists($fqn) && !in_array($options[$arg]['key'], $this->options, true)) {

                            /**
                             * Calculate the delegate cli string and register the
                             * elements already processed to accelerate the parse
                             */
                            $childArgv = array_slice($argv, $index + 1);
                            for ($i = count($argv) - 1; $i >= $index; $i--) {
                                $processed[] = $argv[$i];
                            }
                            $this->options['command'] = new $fqn($childArgv);
                        }

                    } else {

                        /**
                         * calculate the position of : character if is present we
                         * assume that the following characters until | are the number
                         * of parameters in cli string that we have to assign to the
                         * option value
                         */
                        $pointer = strpos($options[$arg]['key'], ':');
                        if ($pointer === false) {

                            /**
                             * if we don't have the : character the option is
                             * a true/false arguments... a flag.
                             */
                            $this->options[$options[$arg]['param']] = true;
                            $processed[] = $argv[$index];

                        } else {

                            /**
                             * get the number of parameter we have to assign as option value,
                             * also if we have defined N elements and they are not present in the
                             * cli string, we throw an exception.
                             */
                            $number = substr($options[$arg]['key'], $pointer + 1) === false ? 1 : substr($options[$arg]['key'], $pointer + 1);
                            if (!isset($argv[$index + 1])) {
                                throw new \Exception(strtr('The option {key} MUST have a value.', array(
                                    '{key}' => trim($options[$arg]['key'], ':0123456789')
                                )));
                            }

                            /**
                             * Save the actual option values and register the
                             * elements already processed to accelerate the parse
                             */
                            $this->options[$options[$arg]['param']] = array_diff(array_slice($argv, $index + 1, $number), $processed);
                        }
                    }
                }
            }

            /**
             * calculate the difference between the cli string ($argv)
             * and the processed elements, the result is the common
             * input options.
             */
            $this->options['input'] = array_diff($argv, $processed);

        } catch (\Exception $e) {

            /**
             * if something goes wrong we log the error and
             * exist the program.
             */
            $this->write($e->getMessage());
            exit("CLI System unable to start.\n");

        }
    }

    /**
     * Show help message
     * @return string
     */
    public function help()
    {
        $help = array($this->description() . "\n" . static::NAME . " v" . static::VERSION . "\n");

        if (isset($this->main)) {
            $help[] = "Syntax: \n   php " . $this->main . " [options] [arguments] [sub-command] [options] [arguments]\n";
        }

        /**
         * parse the arguments block to render de help message
         * with the available arguments.
         */
        $commands = array(" Commands:");
        foreach ($this->arguments as $key => $value) {
            if (strpos($key, '-') === false) {
                $commands[] = "   " . strtolower($value);
            }
        }

        if (count($commands) > 1) {
            $help[] = implode("\n", $commands) . "\n";
        }

        $options = array(" Options:");
        foreach ($this->arguments as $key => $value) {
            if (strpos($key, '-') !== false) {
                $options[] = "   " . $key . " " . ucfirst($value);
            }
        }

        if (count($options) > 1) {
            $help[] = implode("\n", $options) . "\n";
        }


        return implode("\n", $help) . "\n";
    }

    /**
     * Write a text line
     * @param string $msg
     * @param array $context
     * @param bool $onScreen
     * @return string
     */
    public function write($msg, array $context = array(), $onScreen = true)
    {
        $message = strtr($msg, $context) . "\n";
        if ($onScreen === true) {
            print $message;
        }
        return $message;
    }

    /**
     * Get a input from the CLI
     * @param string $message
     * @param string $default
     * @param bool $required
     * @param array $context
     * @return string
     */
    public function input($message, $default = null, $required = false, array $context = array())
    {
        do {

            if ($required === true && !empty($default)) {
                $message .= ' [' . $default . ']';
            }

            $value = readline($this->write($message, $context, false) . ': ');

            if (empty($value) && !empty($default)) {
                $value = $default;
            }

        } while ($required === true && empty($value));

        return trim($value);
    }

    /**
     * Main execution method, here the system
     * route to the proper methods or arguments.
     * @return mixed
     */
    public function execute()
    {
        try {

            /**
             * if the help flag (-h) is present we show
             * the help message.
             */
            if (isset($this->options['help'])) {
                print $this->help();

                return 0;

            } else {

                /**
                 * if a sub-command is defined we run it and
                 * save the result if it, then we pass the result
                 * to the current command (callback system).
                 */
                $payload = null;
                if (isset($this->options['command'])) {
                    $payload = $this->options['command']->execute();
                }

                return $this->run($payload);
            }


        } catch (\Exception $e) {

            /**
             * if something goes wrong we log the error and
             * exist the program.
             */
            $this->write('> ' . $e->getMessage());

            return -1;
        }
    }

    /**
     * Main code execution
     * @param mixed $payload
     * @return mixed
     */
    public function run($payload = null)
    {
        /**
         * this command only redirect and launch
         * other sub-commands.
         */
        print $this->help();

        return 0;
    }
}