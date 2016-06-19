<?php
namespace Laravolt\Mural;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{

    use SoftDeletes;

    protected $with = ['author'];

    public function author()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function scopeBeforeId($query, $beforeId)
    {
        return $query->where('id', '<', $beforeId);
    }

    public function scopeSiblingsAndSelf($query)
    {
        return $query->where('commentable_id', $this->commentable_id)->where('commentable_type', $this->commentable_type);
    }
}
