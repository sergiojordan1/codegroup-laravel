<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfirmedPlayer extends Model
{
    use HasFactory;

    protected $table = 'confirmed_players';
    
    protected $fillable = ['game_id', 'player_id'];
}
