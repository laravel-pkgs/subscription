<?php

namespace IICN\Subscription\Services\Transaction;

class TransactionalData implements \IICN\Subscription\TransactionalData
{

    public function additionalData(): array
    {
        return [
            'version' => request()->hasHeader('av') ? request()->header('av') : null
        ];
    }
}
