<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'to',
        'from',
        'attention',
        'departments',
        'action_items',
        'additional_actions',
        'additional_notes',
        'file_type',
        'files',
    ];

    // Cast JSON fields to arrays
    protected $casts = [
        'departments' => 'array',
        'action_items' => 'array',
        'additional_actions' => 'array',
        'files' => 'array',
    ];

    public function statuses()
    {
        return $this->hasMany(\App\Models\CommunicationFormStatus::class);
    }
}