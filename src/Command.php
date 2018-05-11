<?php

namespace OtherCode\CLI;


/**
 * Class Command
 * @package OtherCode\CLI
 */
abstract class Command
{
    /**
     * Application name
     */
    const NAME = 'CLI';

    /**
     * Current version
     */
    const VERSION = '1.0.0';

    /**
     * CLI Writer (print messages)
     * @var \OtherCode\CLI\Writer
     */
    protected $writer;

    /**
     * Available sub-commands list
     * @var array
     */
    protected $commands = array();

    /**
     * List of available arguments
     * @var array
     */
    protected $arguments = array();

    /**
     * List of available params
     * @var array
     */
    protected $parameters = array();

    /**
     * Command constructor.
     * @param Writer|null $writer
     */
    public function __construct(\OtherCode\CLI\Writer $writer = null)
    {
        if (!ini_get('date.timezone')) {
            ini_set('date.timezone', 'UTC');
        }

        if (isset($writer)) {
            $this->writer;
        } else {
            $this->writer = new \OtherCode\CLI\Writer();
        }

        /**
         * search in the command folder for all the available
         * files/classes and add them to the arguments list.
         */
        foreach ($this->commands as $name => $class) {
            if (class_exists($class)) {
                $this->arguments[strtolower($name) . '|' . $name] = $name;
            }
        }

        /**
         * Append the default arguments to the arguments list
         *  -h help Show help information.
         */
        $this->arguments = array_merge($this->arguments, array(
            '-h|help' => 'Show help information.',
        ));
    }

    /**
     * Bootstrap the command application (arguments, sub-commands, etc)
     * @param array $argv
     * @return $this
     */
    final public function bootstrap(array $argv = array())
    {
        try {

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
                 * search the current element of the cli string in the registered key words (arguments),
                 * if it exists we get the "type" of the keyword if it have - it is an option if not try
                 * to find a command that match, finally if the element is'nt a command or option, we
                 * assume is a common input argument.
                 */
                if (array_key_exists($arg, $options) && !in_array($arg, $processed)) {
                    if (strpos($options[$arg]['key'], '-') === false) {

                        /**
                         * Search for a command for the current element of the
                         * cli string if it exists we instantiate it and delegate
                         * the rest of the cli string.
                         */
                        $fqn = trim('\\' . $this->commands[$options[$arg]['param']]);
                        if (class_exists($fqn) && !in_array($options[$arg]['key'], $this->parameters, true)) {

                            /**
                             * Calculate the delegate cli string and register the
                             * elements already processed to accelerate the parse
                             */
                            $childArgv = array_slice($argv, $index + 1);
                            for ($i = count($argv) - 1; $i >= $index; $i--) {
                                $processed[] = $argv[$i];
                            }

                            /**
                             * instantiate the sub-command class and bootstrap it with
                             * the child arguments (the non-process arguments).
                             */
                            $this->parameters['command'] = new $fqn();
                            if (!($this->parameters['command'] instanceof \OtherCode\CLI\Command)) {
                                throw new \RuntimeException("Invalid command class, the command you are trying to run is not a valid command class.");
                            }

                            $this->parameters['command']->bootstrap($childArgv);
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
                            $this->parameters[$options[$arg]['param']] = true;
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
                            $this->parameters[$options[$arg]['param']] = array_diff(array_slice($argv, $index + 1, $number), $processed);
                            $processed[] = $argv[$index];

                            if (count($this->parameters[$options[$arg]['param']]) < $number) {
                                throw new \InvalidArgumentException(strtr('Invalid parameter count for "{option}" option.', array(
                                    '{option}' => $options[$arg]['param']
                                )));
                            }

                            foreach ($this->parameters[$options[$arg]['param']] as $parameter) {
                                $processed[] = $parameter;
                            }

                        }
                    }
                }
            }

            /**
             * calculate the difference between the cli string ($argv)
             * and the processed elements, the result is the common
             * input options.
             */
            $this->parameters['input'] = array_diff($argv, $processed);

        } catch (\Exception $e) {

            /**
             * if something goes wrong we log the error and
             * exist the program.
             */
            $this->writer->error($e->getMessage());
            exit("CLI System unable to start.\n");

        }

        return $this;
    }

    /**
     * Main execution method, here the system
     * route to the proper methods or arguments.
     * @param mixed $payload
     * @return mixed
     */
    final public function execute($payload = null)
    {

        try {

            /**
             * if the help flag (-h) is present we show
             * the help message.
             */
            if (isset($this->parameters['help'])) {
                $this->writer->info($this->help());

                return 0;

            } else {

                /**
                 * launch the method initialize() if exists, this can help us
                 * to prepare or initialize libs or whatever, before the main
                 * code execution.
                 */
                if (method_exists($this, 'initialize')) {
                    $this->initialize();
                }

                $payload = $this->run($payload);

                /**
                 * if a sub-command is defined to call, pass the current result if it exists
                 * if not a simple null will be process, this allow us to share data between
                 * different commands, chain call :D
                 */
                if (isset($this->parameters['command'])) {
                    $payload = $this->parameters['command']->execute($payload);
                }

                /**
                 * launch the method end() if exists, this acts as __destruct() method
                 * can help us to process data after the execution of the chained command
                 */
                if (method_exists($this, 'finish')) {
                    $payload = $this->finish($payload);
                }

                return $payload;
            }

        } catch (\Exception $e) {

            /**
             * if something goes wrong we log the error and
             * exist the program.
             */
            $this->writer->error('> ' . $e->getMessage());

            return -1;
        }
    }

    /**
     * Return the description message
     * @return string
     */
    public function description()
    {
        return 'Slim command line interface builder.';
    }

    /**
     * Show help message
     * @return string
     */
    public function help()
    {
        $help = array($this->description() . "\n" . static::NAME . " v" . static::VERSION . "\n");

        $options = array(" Options:");

        /**
         * parse the arguments block to render de help message
         * with the available arguments.
         */
        foreach ($this->arguments as $key => $value) {
            if (strpos($key, '-') !== false) {
                $options[] = "   " . $key . "  " . ucfirst($value);
            }
        }

        if (count($options) > 1) {
            $help[] = implode("\n", $options) . "\n";
        }

        $commands = array(" Commands:");

        /**
         * parse the commands block to render de help message
         * with the available commands and description.
         */
        foreach ($this->commands as $key => $class) {
            if (strpos($key, '-') === false && class_exists($class, true)) {
                $commands[] = "   " . strtolower($key) . "  " . (new $class)->description();
            }
        }

        if (count($commands) > 1) {
            $help[] = implode("\n", $commands) . "\n";
        }

        return implode("\n", $help) . "\n";
    }

    /**
     * Main code execution
     * @return mixed
     */
    public function run()
    {
        if (!isset($this->parameters['command'])) {

            /**
             * this command only redirect and launch
             * other sub-commands.
             */
            $this->writer->info($this->help());
        }

        return 0;
    }
}
