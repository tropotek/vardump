<?php
namespace App\Debug {

    use Psr\Log\LoggerInterface;
    use Psr\Log\NullLogger;

    /**
     * Class VarDump, used by the vd(), vdd() functions.
     *
     * @author http://www.tropotek.com/
     */
    class VarDump
    {
        /**
         * @var VarDump
         */
        public static $instance = null;

        /**
         * @var string
         */
        protected $sitePath = '';

        /**
         * @var LoggerInterface|null
         */
        protected $logger = null;


        public function __construct(?LoggerInterface $logger = null, string $sitePath = '')
        {
            $this->logger = $logger;
            $this->sitePath = $sitePath;
        }

        public static function getInstance(?LoggerInterface $logger = null, string $sitePath = ''): ?self
        {
            if (static::$instance == null) {
                if (!$logger) {
                    $logger = new NullLogger();
                }
                if (!$sitePath) {
                    $sitePath = dirname(__FILE__, 4);
                }
                static::$instance = new static($logger, $sitePath);
            }
            return static::$instance;
        }

        public function getSitePath(): string
        {
            return $this->sitePath;
        }

        public function setSitePath(string $sitePath): self
        {
            $this->sitePath = $sitePath;
            return $this;
        }

        public function getLogger(): ?LoggerInterface
        {
            return $this->logger;
        }

        public function setLogger(?LoggerInterface $logger): self
        {
            $this->logger = $logger;
            return $this;
        }

        public function makeDump(array $args, bool $showTrace = false): string
        {
            $str = $this->argsToString($args);
            if ($showTrace) {
                $str .= "\n" . StackTrace::getBacktrace(4, $this->sitePath) . "\n";
            }
            return $str;
        }

        public function argsToString(array $args): string
        {
            $output = '';
            foreach ($args as $var) {
                $output .= self::varToString($var) . "\n";
            }
            return $output;
        }

        /**
         * return argument types array
         *
         * @param mixed $args
         */
        public function getTypeArray($args): array
        {
            $arr = [];
            foreach ($args as $a) {
                $type = gettype($a);
                if ($type == 'object') {
                    $type = str_replace("\0", '', get_class($a));
                }
                $arr[] = $type;
            }
            return $arr;
        }


        /**
         * convert a value/object to a loggable string
         */
        public static function varToString($var, int $depth = 5, int $nest = 0): string
        {
            $pad = str_repeat('  ', $nest * 2 + 1);

            $type = 'native';
            $str = $var;

            if ($var === null) {
                $str = '{NULL}';
            } else if (is_bool($var)) {
                $type = 'Boolean';
                $str = $var ? '{true}' : '{false}';
            } else if (is_string($var)) {
                $type = 'String';
                $str = str_replace("\0", '|', $var);
            } else if (is_resource($var)) {
                $type = 'Resource';
                $str = get_resource_type($var);
            } else if (is_array($var)) {
                $type = sprintf('Array[%s]', count($var));
                $a = array();
                if ($nest >= $depth) {
                    $str = $type;
                } else {
                    foreach ($var as $k => $v) {
                        $a[] = sprintf("%s[%s] => %s\n", $pad, $k, self::varToString($v, $depth, $nest + 1));
                    }
                    $str = sprintf("%s \n%s(\n%s\n%s)", $type, substr($pad, 0, -2), implode('', $a), substr($pad, 0, -2));
                }
            } else if (is_object($var)) {
                $class = str_replace("\0", '', get_class($var));
                $type = '{' . $class . '} Object';
                if ($nest >= $depth) {
                    $str = $type;
                } else {
                    $a = array();
                    foreach ((array)$var as $k => $v) {
                        $k = str_replace($class, '*', $k);
                        $a[] = sprintf("%s[%s] => %s", $pad, $k, self::varToString($v, $depth, $nest + 1));
                    }
                    $str = sprintf("%s \n%s{\n%s\n%s}", $type, substr($pad, 0, -2), implode("\n", $a), substr($pad, 0, -2));
                }
            }
            return $str;
        }
    }
}

namespace { // global code
    use App\Debug\VarDump;

    /**
     * Send a value or a list of values to the logger
     */
    function vd(): string
    {
        $vd = VarDump::getInstance();
        $line = current(debug_backtrace());
        $path = str_replace($vd->getSitePath(), '', $line['file']);
        $str = "\n";
        //$str = sprintf('vd(%s [%s])', $path, $line['line']) . "\n";
        $str .= $vd->makeDump(func_get_args());
        $str .= sprintf('vd(%s) %s [%s];', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";
        $vd->getLogger()->debug($str);
        return $str;
    }

    /**
     * Send a value or a list of values to the logger
     */
    function vdd(): string
    {
        $vd = VarDump::getInstance();
        $line = current(debug_backtrace());
        $path = str_replace($vd->getSitePath(), '', $line['file']);
        $str = "\n";
        $str .= $vd->makeDump(func_get_args(), true);
        $str .= sprintf('vdd(%s) %s [%s]', implode(', ', $vd->getTypeArray(func_get_args())), $path, $line['line']) . "\n";
        $vd->getLogger()->debug($str);
        return $str;
    }

}