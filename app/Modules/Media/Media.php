<?php

namespace App\Modules\Media;

use App\Modules\Post\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'url',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isReel(): bool
    {
        return $this->type === 'reel';
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeReels($query)
    {
        return $query->where('type', 'reel');
    }

    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }
}