<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostDetail extends Model
{
    use HasFactory;
    public $user_name = false;
    public $user_id = false;
    public $user_image = false;
    public $title = false;
    public $description = false;
    public $image = false;
    public $comments_id = false;
    public $primaryKey  = 'post_id';
    public $table = "PostDetail";
}
