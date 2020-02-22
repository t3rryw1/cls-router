<?php
/**
 * Created by PhpStorm.
 * User: terry
 * Date: 2019-01-04
 * Time: 10:34
 */

namespace Laura\Lib\Request;

interface IRequestHandler
{
    /**
     * @param array $data
     * @param array $headers
     * @return mixed
     */
    public function execute(&$data, $headers);

    /**
     * @param IAuthenticator $authenticator
     * @return self
     */
    public function setAuthenticator($authenticator);

    /**
     * @return IAuthenticator
     */
    public function getAuthenticator();

}
