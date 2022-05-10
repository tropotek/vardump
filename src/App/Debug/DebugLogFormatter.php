<?php
namespace App\Debug;

use Monolog\Formatter\LineFormatter;

/**
 * Class LogLineFormatter
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class DebugLogFormatter extends LineFormatter
{
    //const APP_FORMAT = "[%datetime%]%post% %channel%.%level_name%: %message% %context% %extra%\n";
    const APP_FORMAT = "[%datetime%]%post% %level_name%: %message% %context% %extra%\n";

    protected $scriptTime = 0;

    protected $colorsEnabled = false;

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = 'H:i:s.u', $allowInlineLineBreaks = true, $ignoreEmptyContextAndExtra = true)
    {
        $this->scriptTime = microtime(true);
        $format = $format ?: static::APP_FORMAT;
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record) :string
    {
        $colors = array(
            'emergency'     => 'brown',
            'alert'         => 'yellow',
            'critical'      => 'red',
            'error'         => 'light_red',
            'warning'       => 'light_cyan',

            'notice'        => 'light_purple',
            'info'          => 'white',
            'debug'         => 'light_gray'
        );
        $abbrev = array(
            'emergency'     => 'EMR',
            'alert'         => 'ALT',
            'critical'      => 'CRT',
            'error'         => 'ERR',
            'warning'       => 'WRN',
            'notice'        => 'NTC',
            'info'          => 'INF',
            'debug'         => 'DBG'
        );

        $levelName = $record['level_name'];
        $record['level_name'] = $abbrev[strtolower($levelName)];

        if ($this->isColorsEnabled())
            $record['message'] = self::getCliString($record['message'], $colors[strtolower($levelName)]);

        $output = parent::format($record);
        $pre = sprintf('[%9s]', self::bytes2String(memory_get_usage(false)));
        return str_replace('%post%', $pre, $output);
    }

    /**
     * @param $t
     * @return $this
     */
    public function setScriptTime($t)
    {
        if ($t)
            $this->scriptTime = $t;
        return $this;
    }

    /**
     * Get the current script running time in seconds
     *
     * @return string
     */
    public function scriptDuration()
    {
        return (string)(microtime(true) - $this->scriptTime);
    }

    /**
     * @return bool
     */
    public function isColorsEnabled()
    {
        return $this->colorsEnabled;
    }

    /**
     * @param bool $colorsEnabled
     * @return DebugLogFormatter
     */
    public function setColorsEnabled($colorsEnabled)
    {
        $this->colorsEnabled = $colorsEnabled;
        return $this;
    }

    /**
     * Convert a value from bytes to a human readable value
     *
     * @param int $bytes
     * @param int $round
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    public static function bytes2String($bytes, $round = 2)
    {
        $tags = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $index = 0;
        while ($bytes > 999 && isset($tags[$index + 1])) {
            $bytes /= 1024;
            $index++;
        }
        $rounder = 1;
        if ($bytes < 10) {
            $rounder *= 10;
        }
        if ($bytes < 100) {
            $rounder *= 10;
        }
        $bytes *= $rounder;
        settype($bytes, 'integer');
        $bytes /= $rounder;
        if ($round > 0) {
            $bytes = round($bytes, $round);
            return  sprintf('%.'.$round.'f %s', $bytes, $tags[$index]);
        } else {
            return  sprintf('%s %s', $bytes, $tags[$index]);
        }
    }

    /**
     * @var array
     */
    public static $cliFgColorChart = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'green' => '1;34',
        'light_green' => '0;32',
        'cyan' => '1;32',
        'light_cyan' => '0;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37'
    );

    /**
     * @var array
     */
    public static $cliBgColorChart = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47'
    );
    /**
     * Returns colored string for use in CLI scripts
     *
     * @param $string
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return string
     */
    public static function getCliString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $cString = '';
        if (isset(self::$cliFgColorChart[$foregroundColor])) {  // Check if given foreground color found
            $cString .= "\033[" . self::$cliFgColorChart[$foregroundColor] . "m";
        }
        if (isset(self::$cliBgColorChart[$backgroundColor])) {  // Check if given background color found
            $cString .= "\033[" . self::$cliBgColorChart[$backgroundColor] . "m";
        }
        // Add string and end coloring
        $cString .= $string . "\033[0m";
        return $cString;
    }

}