<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;
    const user_id = null;
    const comment = null;
    public $primaryKey  = 'comment_id';
}
