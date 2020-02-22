<?php


namespace Laura\Lib\Request;


trait HasErrorCodeDescription
{
    protected $errorCode;

    protected $errorDescription;


    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    public function setError($error, $desc)
    {
        $this->errorCode = $error;
        $this->errorDescription = $desc;
        return $this;
    }
}
