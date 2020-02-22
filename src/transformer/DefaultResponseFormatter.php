<?php


namespace Laura\Lib\Request;


class DefaultResponseFormatter implements IResponseFormatter
{


    public function formatData($data)
    {

        return json_encode($data);
    }

    public function formatError($errorCode, $errorDesc)
    {
        return json_encode([
            'success' => false,
            'data' => null,
            'error' => array(
                'code' => $errorCode,
                'msg' => $errorDesc
            )
        ]);

    }
}
