# Command Line Framework

Small command line framework to build multi-level chained commands. Offer a small writter and formatter system to print 
messages with custom format (colors and style like bold or underline).

## Requirements

* PHP >= 5.4.*
* PHP Readline extension

## Usage

First we need to create an application: `\App\CLIApplication.php`

```
<?php 

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
```

Nex we create the entry point: `\app.php`

```
<?php

/**
 * Include the core library
 */
require_once '../autoload.php';

/**
 * Include the app files (commands and libs)
 */
require_once 'App/CLIApplication.php';
require_once 'App/Commands/HelloCommand.php';

/**
 * Command line entry point
 */
$app = new Sample\App\CLIApplication();
$app->bootstrap($argv);
$app->execute();
```

Finally we can add all the sub-commands we want, for example:

```
<?php

namespace Sample\App\Commands;

/**
 * Class HelloCommand
 * @package Sample\App
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
        $this->writter->info('Hello World Info');

        $this->writter->input("do yo like it?", 'n', true);
    }
}
```

Our main code is located in the `run()` method, we also can put some code into `initialize()` and `finish()` methods. the
`initialize()` method is executed before the `run()` method, and the `finish()` method is executed after the run method.

The output of the `run()` is passed to the child command so we can use it as argument. Finally the output of the child command is
passed again to the parent command in the `finish()` method.

The only required method top execute a command is `run()`.

Its time to run the command:

```
$ php app.php

Sample CLI Application.
SampleCLIApplication v1.0.0

 Options:
   -h|help  Show help information.

 Commands:
   hello  Say hello world!
```

Running the sub-command.
```   
$ php app.php hello

Hello World Info
do yo like it? [n]: yes

```

