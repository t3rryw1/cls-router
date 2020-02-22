<?php

namespace Laura\Lib\Request;


class ObjectifyTransformer implements IRequestTransformer
{

    public function transform($data, $className = null)
    {
        $requestObject = objectify($className);
        if (!$requestObject) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (!property_exists(get_class($requestObject), $key)) {
                $key = _camelize($key);
                if (!property_exists(get_class($requestObject), $key)) {
                    continue;
                }
            }
            try {
                $reflection = new \ReflectionProperty(get_class($requestObject), $key);
                $reflection->setAccessible(true);
                $reflection->setValue($requestObject, $value);
            } catch (\ReflectionException $e) {
                continue;
            }
        }
        return $requestObject;
    }
}
