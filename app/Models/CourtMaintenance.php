<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtMaintenance extends Model
{
    protected $table = 'court_maintenance';
    protected $fillable = ['court_id', 'date', 'slot', 'reason'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}
