<?php

namespace OtherCode\CLI;


/**
 * Class Writer
 * @package OtherCode\CLI
 */
class Writer
{
    /**
     * Message formatter (colors and styles)
     * @var \OtherCode\CLI\Formatter
     */
    protected $formatter;

    /**
     * Writter constructor.
     * @param Formatter $formatter
     */
    public function __construct(\OtherCode\CLI\Formatter $formatter = null)
    {
        if (isset($formatter)) {
            $this->formatter;
        } else {
            $this->formatter = new \OtherCode\CLI\Formatter();
        }
    }

    /**
     * Set the text formatter
     * @param \OtherCode\CLI\Formatter $formatter
     */
    public function setFormatter(\OtherCode\CLI\Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Get the text formatter
     * @return \OtherCode\CLI\Formatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Write a text line
     * @param string $message
     * @param array $context
     * @param string $style
     * @return void
     */
    public function write($message, array $context = array(), $style = 'none')
    {
        $replace = array();
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = $value;
        }

        print $this->formatter->format(strtr($message, $replace), $style);
    }

    /**
     * Write a text line with new line at the end
     * @param string $message
     * @param array $context
     * @param string $style
     * @return void
     */
    public function writeln($message, array $context = array(), $style = 'none')
    {
        $this->write($message . "\n", $context, $style);
    }

    /**
     * Print a emergency message
     * @param string $message
     * @param array $context
     */
    public function emergency($message, $context = array())
    {
        $this->writeln($message, $context, 'strong_red');
    }

    /**
     * Print an alert message
     * @param string $message
     * @param array $context
     */
    public function alert($message, $context = array())
    {
        $this->writeln($message, $context, 'strong_yellow');
    }

    /**
     * Print a critical message
     * @param string $message
     * @param array $context
     */
    public function critical($message, $context = array())
    {
        $this->writeln($message, $context, 'strong_red');
    }

    /**
     * Print an error message
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = array())
    {
        $this->writeln($message, $context, 'red');
    }

    /**
     * Print a warning message
     * @param string $message
     * @param array $context
     */
    public function warning($message, $context = array())
    {
        $this->writeln($message, $context, 'yellow');
    }

    /**
     * Print a notice message
     * @param string $message
     * @param array $context
     */
    public function notice($message, $context = array())
    {
        $this->writeln($message, $context, 'white');
    }

    /**
     * Print a info message
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = array())
    {
        $this->writeln($message, $context, 'none');
    }

    /**
     * Print a debug message
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, $context = array())
    {
        $this->writeln($message, $context, 'grey');
    }

    /**
     * Print a success message
     * @param string $message
     * @param array $context
     */
    public function success($message, $context = array())
    {
        $this->writeln($message, $context, 'green');
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

            if ($required === true && isset($default)) {
                $message .= ' [' . $default . ']';
            }

            $value = readline($this->write($message . ":", $context, 'white') . ' ');

            if (!isset($value) && isset($default)) {
                $value = $default;
            }

        } while ($required === true && !isset($value));

        return trim($value);
    }


}