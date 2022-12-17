<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;
    const fullname = null;
    const users_followed_id = null;
    const users_following_id = null;
    const about = null;
    const phone_number = null;
    const address = null;
    const avatar = null;
    const account_email = null;
    const account_pwd = null;
    public $table = "users";
    public $primaryKey  = 'user_id';

}
