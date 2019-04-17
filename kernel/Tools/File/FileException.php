<?php
namespace Kernel\Tools\Collection;

class FileException extends \Exception
{
    const FILE_ERROR = 700;
    const EXTENSION_NOT_ALLOWED = 701;
    const LIMIT_OF_SIZE_REACHED = 702;
    const DIMENSION_NOT_ALLOWED = 703;
}
