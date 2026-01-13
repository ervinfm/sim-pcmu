<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory;

    // Arahkan spesifik ke tabel 'post_categories'
    protected $table = 'post_categories'; 

    protected $guarded = ['id'];

    /**
     * Relasi: Satu Kategori memiliki banyak Postingan
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }
}