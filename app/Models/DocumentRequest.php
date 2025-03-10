<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'status',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
