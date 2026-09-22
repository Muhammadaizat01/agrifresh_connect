<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceIndex extends Model
{
    protected $table = 'price_index';
    public $timestamps = false;
    protected $fillable = ['crop_name', 'direct_price', 'middleman_price', 'farmer_gain', 'trend'];
}
