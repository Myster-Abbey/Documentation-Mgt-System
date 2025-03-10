<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'uploaded_by'
    ];


    // Relation to get the user who uploaded the document
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function users()
    {
        // return $this->belongsToMany(User::class, 'document_requests')
        //             ->withPivot('status')
        //             ->withTimestamps();
        return $this->belongsToMany(User::class,);
    }
}
