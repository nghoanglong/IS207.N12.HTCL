<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Client;

class MongoDB extends Model
{
    use HasFactory;
    private $client;
    public $db;
    public function  __construct(){
        $this->client = new Client(
            'mongodb+srv://sanke2211:FvgMPpstfXJaJO7i@cluster0.nlul1.mongodb.net/myFirstDatabase?retryWrites=true&w=majority'
        );
        $this->db = $this->client->WebProject;
    }
}
