<?php
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$sitePath = dirname(__FILE__);
$composer = include($sitePath . '/vendor/autoload.php');
$LOG_LEVEL = Logger::DEBUG;     // vd(),vdd() are sent with this log level
//$LOG_LEVEL = Logger::INFO;
//$LOG_LEVEL = Logger::NOTICE;
//$LOG_LEVEL = Logger::WARNING;
//$LOG_LEVEL = Logger::ERROR;
//$LOG_LEVEL = Logger::CRITICAL;
//$LOG_LEVEL = Logger::ALERT;
//$LOG_LEVEL = Logger::EMERGENCY;

// ------------- init -------------
$logPath = __DIR__ . '/debug.log';
//$logger = new \Psr\Log\NullLogger();
$logger = new Logger('debug');
$handler = new StreamHandler($logPath, $LOG_LEVEL);
$logger->pushHandler($handler);
$formatter = new \App\Debug\DebugLogFormatter();
// Enable ASCII text colors
$formatter->setColorsEnabled(true);   //Do not enable if you are going to use grep commands to search the log file
$handler->setFormatter($formatter);
\App\Debug\VarDump::getInstance($logger, $sitePath);
// --------------------------------

// Logging examples
$logger->debug('This is a Debug Message');
$logger->emergency('This is an Emergency Message');
$logger->error('This is an Error Message');


// Custom var dump functions vd(), vdd() should show nothing on fully released sites.
// Var dump Examples
vd('testing');
// Arrays
vdd(['test1' => 'test1', 'test2' => ['test2.1' => 'test2.1'], 'test3' => 'test3']);
// Objects
$std = new stdClass();
$std->var1 = 1;
$std->var2 = 2;
vd($std);
// With stack trace
vdd($std);

?>
<html>
<head>
    <title>Log Test</title>
</head>
<body>
    <h2>Log Test</h2>
    <p>Tail the log with `<code>tail -f <?= $logPath ?></code>`</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</body>
</html>
