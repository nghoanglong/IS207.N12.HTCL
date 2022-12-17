<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    use HasFactory;
    public $post_ref_id = false;
    public $user_id = false;
    public $categories_id = false;
    public $comments_id = false;
    public $post_type = false;
    public $image = false;
    public $title = false;
    public $description = false;
    public $table = "Posts";
    protected $primaryKey = 'post_id';
    public $incrementing = false;
}
