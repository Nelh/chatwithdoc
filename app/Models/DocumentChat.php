<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([UserScope::class])]
class DocumentChat extends Model
{
    protected $fillable = [
        'uuid',
        'document_id',
        'question',
        'answer',
        'meta',
        'user_id',
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
