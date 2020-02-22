<?php
/**
 * Created by PhpStorm.
 * User: terry
 * Date: 2019-01-04
 * Time: 10:35
 */

namespace Laura\Lib\Request;

interface IRequestValidator extends IRuleValidator
{


    public function shouldAuthenticate();

    /**
     * @param array $body
     * @param array $headers
     * @return bool
     */
    public function validate(&$body, $headers);

    /**
     * @param $code
     * @param $description
     * @return self
     */
    public function setError($code, $description);


    /**
     * @return int
     */
    public function getErrorCode();


    public function getDefaultErrorCode();

    public function getDefaultErrorDescription();

    /**
     * @return string
     */
    public function getErrorDescription();

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
