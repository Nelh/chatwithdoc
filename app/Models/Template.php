<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'title',
        'content',
        'category',
        'primary_color',
        'secondary_color',
        'background',
        'status'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
