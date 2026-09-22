<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $fillable = ['order_id', 'product_name', 'quantity', 'unit', 'price', 'subtotal'];
    public $timestamps = false;
}
