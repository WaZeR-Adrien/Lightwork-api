<?php
namespace Kernel\Loggers;

class Logger
{
    const DEFAULT_PATH = "logs/";

    const LOG_DEBUG = "DEBUG";
    const LOG_INFO = "INFO";
    const LOG_ALERT = "ALERT";
    const LOG_CRITICAL = "CRITICAL";
    const LOG_ERROR = "ERROR";
    const LOG_WARNING = "WARNING";


    /**
     * Path start in Logs folder
     * @var string
     */
    protected $path;

    /**
     * Level of the log (DEBUG, CRITICAL, ERROR...)
     * @var string
     */
    protected $level;

    /**
     * ApiLogger constructor.
     * @param string $path
     */
    public function __construct($path, $level = self::LOG_DEBUG)
    {
        $this->path = $path;
        $this->level = $level;
    }

    public static function getInstance($level = self::LOG_DEBUG)
    {
        return new self(Logger::DEFAULT_PATH . date("d-m-Y") . ".log", $level);
    }

    /**
     * Save a new log
     * @throws LogException
     */
    public function write($message)
    {
        try {
            $date = date("d/m/Y H:i:s");

            if (strpos($this->path, ".csv")) {
                $begin = "$date, $this->level, ";
            } else {
                $begin = "[$date] [$this->level] ";
            }

            file_put_contents($this->path, $begin . $message, FILE_APPEND);
        } catch (\Exception $e) {
            throw new LogException($e->getMessage(), LogException::ERROR_DURING_PUT_IN_FILE);
        }
    }

    /**
     * Get all logs
     */
    public function getAll()
    {
        $logs = self::parse();

        return $logs;
    }

    /**
     * Parse the log file to retrieve the content
     * @return array
     */
    private function parse()
    {
        try {
            $file = fopen($this->path, 'r');

            // lines
            $logs = [];
            while (($line = fgets($file)) !== FALSE) {
                $logs[] = $line;
            }

            fclose($file);
        } catch (\Exception $e) {
            throw new LogException("The file can't be parsed", LogException::CANT_PARSE_FILE);
        }

        return $logs;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }
}
