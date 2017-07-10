<?php

namespace Owja\ImageProxyBundle\Exception;

use Throwable;

class ConfigurationException extends \Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}