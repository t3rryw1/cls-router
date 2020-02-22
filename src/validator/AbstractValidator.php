<?php

namespace Laura\Lib\Request;

abstract class AbstractValidator extends RuleValidator implements IRequestValidator, IQuerySnippetExecutor
{
    use HasAuthenticator, HasErrorCodeDescription;

    public function shouldAuthenticate()
    {
        return false;
    }

    /**
     * @param array $body
     * @param array $headers
     * @return bool
     */
    public function validate(&$body, $headers)
    {
        return $this->validateRuleExpressions($this->getValidationRules(), $body, $headers);

    }

    public function getDefaultErrorCode()
    {
        return 400;
    }

    protected function getValidationRules()
    {
        return [];
    }

    public function getDefaultErrorDescription()
    {
        return "unknown_error";
    }


}
