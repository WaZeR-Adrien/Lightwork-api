<?php
namespace Kernel\Tools\Collection;

class OrmException extends \Exception
{
    const CONNECTION = 100;

    // TODO : Chaque type d'exception comme sur une centaine
    // Ex: pour l'orm, c'est 100, pour les collections, c'est 200....
}
