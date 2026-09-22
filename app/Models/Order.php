<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id', 'order_number', 'buyer_name', 'buyer_role', 'phone', 'total_amount',
        'status', 'payment_method', 'shipping_address', 'notes', 'batch_code', 'driver'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
