<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
   
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'status',
    ];

    public function readingAreas()
    {
   
        return $this->belongsToMany(ReadingArea::class, 'area_facilities', 'facility_id', 'area_id');
    }
}
