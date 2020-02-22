<?php


namespace Laura\Lib\Request;


interface IResponseFormatter
{

    public function formatData($data);

    public function formatError($errorCode,$errorDesc);
}
