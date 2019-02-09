<?php
namespace Kernel\Logs;

class Log
{
    const FILE_NAME = 'logs.csv';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $key;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $ip;

    /**
     * Log constructor.
     * @param string $code
     * @param string $key
     * @param date $date
     * @param int $status
     * @param string $method
     * @param string $endpoint
     * @param string $ip
     */
    public function __construct($code, $key, $date, $status, $method, $endpoint, $ip)
    {
        $this->code = $code;
        $this->key = $key;
        $this->date = $date;
        $this->status = $status;
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->ip = $ip;
    }

    /**
     * Save a new log
     */
    public function save()
    {
        file_put_contents(self::FILE_NAME, "\n$this->code, $this->key, $this->date, $this->status, $this->method, $this->endpoint, $this->ip", FILE_APPEND);
    }

    /**
     * Get all logs
     */
    public static function getAll()
    {
        $logs = self::parse();

        return $logs;
    }

    /**
     * Get logs with the method sent in GET
     * @param $params
     * @return array
     */
    public static function getByMethod($params)
    {
        $logs = [];
        foreach (self::parse() as $log) {
            if ($log['method'] == strtoupper($params->method)) {
                $logs[] = $log;
            }
        }

        return $logs;
    }

    /**
     * Get logs with the date sent in GET
     * @param $params
     * @return array
     */
    public static function getByDate($params)
    {
        $logs = [];
        foreach (self::parse() as $log) {
            if (substr($log['date'], 0, 10) == $params->date) {
                $logs[] = $log;
            }
        }

        return $logs;
    }

    /**
     * Get logs with the method and the date sent in GET
     * @param $params
     * @return array
     */
    public static function getByMethodAndDate($params)
    {
        $logs = [];
        foreach (self::parse() as $log) {
            if ($log['method'] == strtoupper($params->method) && substr($log['date'], 0, 10) == $params->date) {
                $logs[] = $log;
            }
        }

        return $logs;
    }

    /**
     * Parse the log file to retrieve the content
     * @return array
     */
    private static function parse()
    {
        $index = [];
        $file = fopen(self::FILE_NAME, 'r');

        // first line
        $firstLine = fgetcsv($file);
        foreach ($firstLine as $k => $v) {
            $index[trim(strtolower($v))] = $k;
        }

        // other lines
        $logs = [];
        while (($line = fgetcsv($file)) !== FALSE) {
            $logs[] = [
                'code' => trim($line[$index['code']]),
                'date' => trim($line[$index['date']]),
                'status' => trim($line[$index['status']]),
                'method' => trim($line[$index['method']]),
                'endpoint' => trim($line[$index['endpoint']]),
                'ip' => trim($line[$index['ip']])
            ];
        }
        fclose($file);

        return $logs;
    }
}