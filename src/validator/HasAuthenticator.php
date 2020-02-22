<?php


namespace Laura\Lib\Request;


trait HasAuthenticator
{
    /**
     * @var IAuthenticator
     */
    protected $authenticator;

    /**
     * @param IAuthenticator $authenticator
     * @return self
     */
    public function setAuthenticator($authenticator)
    {
        $this->authenticator = $authenticator;
        return $this;
    }

    /**
     * @return IAuthenticator
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

}
