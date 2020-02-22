<?php


namespace Laura\Lib\Request;


interface IRequestTransformer
{

    public function transform($data, $className=null);
}
