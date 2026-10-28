<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['name', 'price', 'unit', 'deposit', 'stock', 'icon', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'stock' => 'integer'];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
