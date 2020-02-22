<?php


namespace Laura\Lib\Request;


interface IRuleValidator
{
    public function countByQuery($sqlStr);

    public function validateRule($rule, $request, $headers, $errorCode = 0, $errorDesc = null);

    public function validateRuleExpressions($ruleExpressions, $request, $headers);


}
