<?php

namespace Owja\ImageProxyBundle\Exception;

use Throwable;

class ProcessingException extends \Exception
{
    public function __construct($message = "Image Processing Error", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}