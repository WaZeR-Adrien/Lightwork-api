<?php
namespace Kernel\Loggers;
use AdrienM\Collection\Collection;
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
        return self::parse()->getAll();
    }

    /**
     * Get logs with the method sent in GET
     * @param string $method
     * @return Collection
     */
    public function getByMethod(string $method): Collection
    {
        return self::parse()->filter(function (array $log) use ($method) {
            return $log["method"] == strtoupper($method);
        });
    }

    /**
     * Get logs with the date
     * @param string $date
     * @return Collection
     */
    public function getByDate(string $date): Collection
    {
        return self::parse()->filter(function (array $log) use ($date) {
            return substr($log['date'], 0, 10) == $date;
        });
    }

    /**
     * Get logs with the method and the date
     * @param string $method
     * @param string $date
     * @return Collection
     */
    public function getByMethodAndDate(string $method, string $date): Collection
    {
        return self::parse()->filter(function (array $log) use ($method, $date) {
            return $log['method'] == strtoupper($method) && substr($log['date'], 0, 10) == $date;
        });
    }

    /**
     * Parse the log file to retrieve the content
     * @return array
     */
    private function parse(): Collection
    {
        try {
            $index = new Collection();
            $file = fopen($this->path . "/" . $this->filename, 'r');

            // first line
            $firstLine = fgetcsv($file);
            foreach ($firstLine as $k => $v) {
                $index->add($k, trim(strtolower($v)));
            }

            // other lines
            $logs = new Collection();
            while (($line = fgetcsv($file)) !== FALSE) {
                $log = [];
                foreach ($index->getAll() as $k => $v) {
                    $log[$k] = trim($line[$v]);
                }
                $logs->add($log);
            }
            fclose($file);
        } catch (\Exception $e) {
            throw new LogException("The file can't be parsed", LogException::CANT_PARSE_FILE);
        }

        return $logs;
    }

}
