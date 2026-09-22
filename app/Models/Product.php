<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    const UPDATED_AT = null;

    protected $fillable = [
        'batch_id', 'farmer_id', 'category_id', 'name', 'name_ms', 'slug',
        'description', 'description_ms', 'harvest_date', 'quantity_available',
        'minimum_order', 'unit', 'price_per_unit', 'middleman_price', 'storage_temp',
        'farm_location', 'grade', 'pesticide_status', 'image_path', 'is_spotlight',
        'spotlight_headline', 'spotlight_subtitle', 'tag', 'approval_status'
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
