# vardump

__Project:__ vardump  
__Published:__ 05 May 2022  
__Web:__ <http://www.tropotek.com/>  
__Author:__ Michael Mifsud <http://www.tropotek.com/>  

A basic example of using Monolog to create some developer 
logging tools to speed up your dev process.

## Contents

- [Installation](#installation)
- [Introduction](#introduction)

## Installation

Just clone the repository locally then run the composer to setup.
Then browse to the index.php pn your system.

~~~bash
$ git clone https://github.com/tropotek/vardump.git
$ cd vardump
$ composer install 
~~~

## Introduction

### Tailing the log

Use your browser to view the index.php. It will log to a file called `debug.log`.

Then you can run the command:
~~~bash
$ tail -f debug.log 
~~~

Now when you reload the page you will see the log in real time.

### Monolog

We will use Monolog to log to a file, there is a custom log formatter in the path
`src/App/Debug/DebugLogFormatter.php`. This formatter allows us to log multiple 
line messages.

You can also enable ASCII colors for different types of messages, it is off by default:

~~~php
$formatter = new \App\Debug\DebugLogFormatter();
$formatter->setColorsEnabled(true);
~~~

### VarDump

When developing it is rather handy to be able to var dump variables fast. Se we have 
implemented 2 global functions `vd()` and `vdd()`.

**vd():** This function will dump standard types and objects in a readable format.

**vdd():** Same as `vd()` but with a full stack trace so you can see how the code got there.


See the [index.php](index.php) for a full usage example.



