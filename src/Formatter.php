<?php

namespace OtherCode\CLI;


/**
 * Class Formatter
 * @package OtherCode\CLI
 */
class Formatter
{
    /**
     * List of Styles
     * @var array
     */
    protected $styles = array(
        'dim' => array('dim' => 1),
        'red' => array('fg' => 'red'),
        'green' => array('fg' => 'green'),
        'white' => array('fg' => 'white'),
        'yellow' => array('fg' => 'yellow'),
        'blue' => array('fg' => 'blue'),
        'grey' => array('fg' => 'grey'),
        'strong_red' => array('fg' => 'red', 'bold' => true),
        'strong_green' => array('fg' => 'green', 'bold' => true),
        'strong_white' => array('fg' => 'white', 'bold' => true),
        'strong_yellow' => array('fg' => 'yellow', 'bold' => true),
        'bold' => array('fg' => 'white', 'bold' => true),
        'underline' => array('fg' => 'white', 'underline' => true)
    );

    /**
     * Available style options
     * @var array
     */
    protected $options = array(
        'bold' => 1,
        'dim' => 2,
        'underline' => 4,
        'blink' => 5,
        'reverse' => 7,
        'conceal' => 8
    );

    /**
     * Available foreground colors
     * @var array
     */
    protected $foreground = array(
        'grey' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
    );

    /**
     * Available background colors
     * @var array
     */
    protected $background = array(
        'grey' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47
    );

    /**
     * True if the color feature is supported
     * @var bool
     */
    protected $supported = false;

    /**
     * Formatter constructor. Check if the color feature is
     * supported by the current CLI system.
     */
    public function __construct()
    {
        $this->supported = DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    /**
     * Set a start point for the style
     * @param string $style
     * @return string
     */
    public function start($style)
    {
        if (!$this->supported || $style == 'none' || !isset($this->styles[$style])) {
            return '';
        }

        $codes = array();

        if (isset($this->styles[$style]['fg'])) {
            $codes[] = $this->foreground[$this->styles[$style]['fg']];
        }

        if (isset($this->styles[$style]['bg'])) {
            $codes[] = $this->background[$this->styles[$style]['bg']];
        }

        foreach ($this->options as $option => $value) {
            if (isset($this->styles[$style][$option]) && $this->styles[$style][$option]) {
                $codes[] = $value;
            }
        }

        return "\033[" . implode(';', $codes) . 'm';
    }

    /**
     * Set the style back to normal
     * @return string
     */
    public function clear()
    {
        if (!$this->supported) {
            return '';
        }

        return "\033[0m";
    }

    /**
     * Format a text message with the desired style
     * @param string $message
     * @param string $style
     * @return string
     */
    public function format($message = '', $style = 'none')
    {
        return $this->start($style) . $message . $this->clear();
    }
}