<?php
namespace Kernel\Loggers;

class LogException extends \Exception
{
    const ERROR_DURING_PUT_IN_FILE = 400;
    const CANT_PARSE_FILE = 401;
}
