<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationFormStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_form_id',
        'status',
    ];

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    public function communicationForm()
    {
        return $this->belongsTo(CommunicationForm::class);
    }
} 