<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingArea extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'floor_number',
        'capacity',
        'description',
        'status',
    ];

    public function facilities()
    {
        // Pivot: area_facilities (area_id, facility_id), tanpa timestamps.
        return $this->belongsToMany(Facility::class, 'area_facilities', 'area_id', 'facility_id');
    }
}
