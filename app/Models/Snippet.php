<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Snippet extends Model
{
        use HasFactory;


    protected $fillable = [
    'title',
    'code',
    'language',
    'user_id',
    'is_public',
    // 'tag_names',
];

public function tags()
{
    return $this->belongsToMany(Tag::class, 'snippet_tag', 'snippet_id', 'tag_id');
    
}

public function user()
{
    return $this->belongsTo(User::class);
}


    //
}
