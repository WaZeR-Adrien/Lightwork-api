<?php
namespace Kernel\Loggers;

class ApiLogger extends Logger
{
    const API_PATH = 'logs/api.csv';

    /**
     * ApiLogger constructor.
     */
    public function __construct($level = parent::LOG_ERROR)
    {
        parent::__construct(self::API_PATH, $level);
    }

    /**
     * Save a new log
     * @throws LogException
     */
    public function write($code, $key, $status, $method, $path, $ip)
    {
        parent::write("$code, $key, $status, $method, $path, $ip");
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
     * Get logs with the method sent in GET
     * @param $params
     * @return array
     */
    public function getByMethod($params)
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
    public function getByDate($params)
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
    public function getByMethodAndDate($params)
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
    private function parse()
    {
        try {
            $index = [];
            $file = fopen($this->path, 'r');

            // first line
            $firstLine = fgetcsv($file);
            foreach ($firstLine as $k => $v) {
                $index[trim(strtolower($v))] = $k;
            }

            // other lines
            $logs = [];
            while (($line = fgetcsv($file)) !== FALSE) {
                $logs[] = [
                    'date' => trim($line[$index['date']]),
                    'level' => trim($line[$index['level']]),
                    'code' => trim($line[$index['code']]),
                    'status' => trim($line[$index['status']]),
                    'method' => trim($line[$index['method']]),
                    'endpoint' => trim($line[$index['endpoint']]),
                    'ip' => trim($line[$index['ip']])
                ];
            }
            fclose($file);
        } catch (\Exception $e) {
            throw new LogException("The file can't be parsed", LogException::CANT_PARSE_FILE);
        }

        return $logs;
    }
}
