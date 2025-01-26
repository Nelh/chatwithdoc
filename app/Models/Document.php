<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

#[ScopedBy([UserScope::class])]
class Document extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'content',
        'context',
        'template',
        'type',
        'file_path',
        'meta',
        'processing_status',
        'status',
        'code',
        'signatures',
        'user_id',
        'expiration_date',
        'expiration_date_reminder'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function($model) {
            if($model) {
                try {
                    $path =  'pdf/' . $model->uuid . 'pdf';
                    Storage::disk(config('filesystems.default'))->delete($path);
                }
                catch(\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Something went wrong')
                        ->body($e->getMessage())
                        ->send();
                }
            }
        });
    }


    protected $casts = [
        'meta' => 'array',
        'code' => 'array',
        'signatures' => 'array',
        'expiration_date_reminder' => 'boolean',
    ];

    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class);
    }

    public function chats()
    {
        return $this->hasMany(DocumentChat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
