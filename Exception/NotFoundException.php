<?php

namespace Owja\ImageProxyBundle\Exception;

use Throwable;

class NotFoundException extends \Exception
{
    public function __construct($message = "Image not Found.", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}