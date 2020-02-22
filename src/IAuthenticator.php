<?php


namespace Laura\Lib\Request;


interface IAuthenticator extends IRuleValidator
{
    public function beforeAuthenticate($body, $headers);

    /**
     * @return bool
     */
    public function isAuthenticated();

    /**
     * @param $body
     * @param $headers
     * @return bool
     */
    public function authenticate($body, $headers);

    /**
     * @return mixed
     */
    public function currentUser();

    /**
     * @return int
     */
    public function currentUserId();

    /**
     * @return int
     */
    public function getErrorCode();

    /**
     * @return string
     */
    public function getErrorDescription();

    /**
     * @param $code
     * @param $description
     * @return self
     */
    public function setError($code, $description);

}
