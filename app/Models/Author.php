<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'bio',
    ];

    public function books()
    {
        // Pivot: book_authors (book_id, author_id), tanpa timestamps.
        return $this->belongsToMany(Book::class, 'book_authors');
    }
}
