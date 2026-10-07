<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharterPost extends Model
{
    use HasFactory;

    // The sheets of the Citizen's Charter book that take posts.
    const SHEETS = ['bulletin', 'events', 'others'];

    // The sheet whose posts may have a picture.
    const PICTURE_SHEET = 'others';

    protected $fillable = [
        'sheet',
        'title',
        'date_text',
        'details',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    // Where the picture of the post is served from (CharterPostController@image),
    // with the time of its last change so that a new picture is not mistaken
    // for the old one a browser has kept.
    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? '/citizens-charter/posts/' . $this->id . '/image?v=' . optional($this->updated_at)->timestamp
            : null;
    }
}
