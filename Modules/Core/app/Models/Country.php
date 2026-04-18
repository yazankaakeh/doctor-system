<?php

namespace Modules\Core\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Country extends Model
{
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'code'];

    public static function getCountriesSelect2(): Collection
    {
        return Country::query()->pluck('name', 'id');
    }
}
