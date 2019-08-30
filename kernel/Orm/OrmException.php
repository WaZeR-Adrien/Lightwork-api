<?php
namespace Kernel\Orm;

class OrmException extends \Exception
{
    const CONNECTION = 100;
    const TYPE_HINT = 101;
}
