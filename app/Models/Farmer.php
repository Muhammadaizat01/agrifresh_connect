<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    protected $table = 'farmers';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'farm_name', 'kedah_district', 'farm_location_details',
        'farming_certification', 'cert_number', 'experience_years', 'rating',
        'famox_tier', 'avatar', 'quote', 'is_approved'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'farmer_id');
    }
}
