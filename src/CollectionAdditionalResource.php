<?php

namespace IICN\Subscription;


interface CollectionAdditionalResource
{
    public static function make($classResources, ...$parameters);

    public static function collection($classResources, $resource);
}
