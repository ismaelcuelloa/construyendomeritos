<?php

namespace App\Models\Traits;

trait Status
{
    public function scopeActive($query, bool $active = true)
    {
        return $query->where('active', $active);
    }

    public function scopePublished($query, bool $published = true)
    {
        return $query->where('published', $published);
    }

    public function scopeVisible($query, bool $visible = true)
    {
        return $query->active($visible)->published($visible);
    }
}
