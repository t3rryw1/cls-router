<?php


namespace Laura\Lib\Request;


abstract class RuleValidator implements IRuleValidator
{
    abstract public function setError($error, $desc);

    public function validateRule($rule, $request, $headers, $errorCode = 0, $errorDesc = null)
    {
        if (is_callable($rule)) {
            try {
                if (!!$rule($request, $headers)) {
                    return true;
                } else {
                    $this->setError($errorCode, $errorDesc);
                    return false;
                }
            } catch (\Exception $e) {
                $this->setError($errorCode ?? $e->getCode(), $errorDesc ?? $e->getMessage());
                return false;

            }
        } elseif (is_string($rule)) {
            return !!$this->stringValidate($rule, $request, $headers);
        }

    }

    public function validateRuleExpressions($ruleExpressions, $request, $headers)
    {
        foreach ($ruleExpressions as $ruleExpression) {
            [$rule, $errorCode, $errorDesc] = $ruleExpression;

            if (!$this->validateRule($rule, $request, $headers, $errorCode, $errorDesc)) {
                return false;
            }
        }
        return true;
    }

    private function extractData($valuePart, $request, $headers)
    {
        $source = strtolower(substr($valuePart, 0, strpos($valuePart, '.')));
        $data = $source === "headers"
            ? $headers : $request;
        $valuePart = str_ireplace(['headers.', 'request.'], '', $valuePart);
        $fieldList = explode('.', $valuePart);
        while ($fieldList && $data) {
            $field = array_shift($fieldList);
            $data = @$data[$field];
        }
        return $data;
    }

    protected function stringValidate($ruleStr, $request, $headers)
    {
        [$valuePart, $operator, $extra] = explode('|', $ruleStr);
        $data = $this->extractData($valuePart, $request, $headers);
        switch (strtolower($operator)) {
            case "require":
                return $data !== null;
            case "in":
                return !empty(@$extra)
                    && (
                        is_null($data)
                        || in_array($data, explode(",", $extra)));
            case "range":
                if (empty(@$extra))
                    return false;

                if (is_null($data)) return true;
                [$min, $max] = explode(',', $extra);
                return (float)$min <= (float)$data
                    && (float)$data <= (float)$max;
            case "type":
                if (empty(@$extra))
                    return false;
                if (is_null($data)) return true;

                switch (strtolower($extra)) {
                    case "number":
                    case "float":
                        return is_numeric($data);
                    case "int":
                        return ctype_digit(strval($data));
                    default:
                        return false;
                }
            case "exists":
                if (empty(@$extra))
                    return false;
                if (is_null($data)) return true;
                [$tableName, $column] = explode('.', $extra);
                /** @noinspection SqlResolve */
                return $this->countByQuery(<<<SQL
select count(*) from `$tableName` where `$column` = '$data'
SQL
                    ) > 0;
            default:
                return false;
        }
    }

    abstract public function countByQuery($sqlStr);


}
