<?php

namespace IICN\Subscription\Http\Resources;

use IICN\Subscription\CollectionAdditionalResource;

class CollectionAdditionalTrait implements CollectionAdditionalResource
{

    public static function make($classResources, ...$parameters)
    {
        $message = "";
        if (isset($parameters[1]) and $parameters[1]) {
            $message = $parameters[1];
        }
        return $classResources->additional(['success' => true, 'message' => $message]);
    }

    public static function collection($classResources, $resource)
    {
        return $classResources->additional(['success' => true, 'message' => ""]);
    }

}
