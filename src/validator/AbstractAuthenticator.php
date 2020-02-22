<?php


namespace Laura\Lib\Request;


abstract class AbstractAuthenticator extends RuleValidator implements IAuthenticator
{
    use HasErrorCodeDescription;

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return false;
    }

    public function beforeAuthenticate($body, $headers)
    {
        return true;
    }

    /**
     * @param $body
     * @param $headers
     * @return bool
     */
    public function authenticate($body, $headers)
    {
        return $this->validateRuleExpressions($this->getValidationRules(), $body, $headers);
    }

    protected function getValidationRules()
    {
        return [];
    }
}
