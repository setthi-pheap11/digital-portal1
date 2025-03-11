<?php
namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use CrudTrait;
    use HasFactory, HasUuids;

    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'product_name',
        'product_detail',
        'priceUSD',
        'category_id',
        'seller_id',
        'image'
    ];

    // A Product belongs to a Seller (User)
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // A Product belongs to a Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}

