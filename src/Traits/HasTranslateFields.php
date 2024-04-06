<?php

namespace IICN\Subscription\Traits;

use Modules\Common\Entities\Language;

trait HasTranslateFields
{
    public function scopeWithLanguage($query, $languageCode = null)
    {
        if (!$languageCode) {
            $languageCode = app()->getLocale();
        }

        $selects = array_map(function ($field) use ($languageCode) {
            return \DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT($field, '$." . $languageCode . "')), JSON_UNQUOTE(JSON_EXTRACT($field, '$.public'))) as {$field}_client");
        }, $this::$translateFields);

        $query->addSelect(['*'])->addSelect($selects);

        if ($this->translateFieldExists("status")) {
            $query->whereJsonContains("status->$languageCode", "1")
                ->orWhere(function ($query) use ($languageCode) {
                    $query->whereJsonContains("status->public", "1")
                        ->whereJsonDoesntContain("status->$languageCode", "0")
                        ->orWhereJsonDoesntContainKey("status->$languageCode");
                });
        }

        return $query;
    }

    public function scopeWithLanguageByOrm($query, $languageCode = null)
    {
        if (!$languageCode) {
            $languageCode = app()->getLocale();
        }

        foreach ($this::$translateFields as $field) {
            $query->addSelect([
                "{$field}->{$languageCode} as {$field}_client",
            ]);
        }

        return $query;
    }

    protected function translateFieldExists($field)
    {
        return \Schema::hasColumn($this->getTable(), $field);
    }
}
