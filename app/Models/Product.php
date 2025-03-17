<?php
namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
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
        'product_claim',
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
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('s3')->url("digital/{$this->image}") : null;
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->product_id)) {
                $model->product_id = (string) Str::uuid();
            }
        });
    }
}

