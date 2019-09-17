<?php
namespace Kernel\Loggers;
use AdrienM\Logger\LogException;
use AdrienM\Logger\Logger;

class HttpLogger extends Logger
{

    /**
     * @param string|null $path
     * @param string $level
     * @return HttpLogger
     */
    public static function getInstance(string $path = null, string $level = self::LOG_DEBUG): Logger
    {
        if (null == $path) {
            $path = dirname(__DIR__, 2) . "/logs/";
        }

        return new self($path, "http.csv", $level);
    }

    /**
     * Get all logs
     * @return array
     */
    public function getAll(): array
    {
        return self::parse();
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
            $file = fopen($this->path . "/" . $this->filename, 'r');

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
