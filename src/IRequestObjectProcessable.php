<?php
/**
 * Created by PhpStorm.
 * User: terry
 * Date: 2019-01-04
 * Time: 10:34
 */

namespace Laura\Lib\Request;

interface IRequestObjectProcessable extends IRequestHandler
{

    public function getRequestClass();
}
