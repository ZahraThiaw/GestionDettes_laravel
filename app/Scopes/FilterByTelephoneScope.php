<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FilterByTelephoneScope implements Scope
{
    protected $telephone;

    public function __construct($telephone)
    {
        $this->telephone = $telephone;
    }

    public function apply(Builder $builder, Model $model)
    {
        if ($this->telephone) {
            $builder->where('telephone', $this->telephone);
        }
    }
}
