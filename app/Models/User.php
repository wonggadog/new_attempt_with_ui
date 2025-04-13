<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_number',
        'name',
        'email',
        'password',
        'department',
        'google_drive_token',
        'google_drive_refresh_token',
        'google_drive_folder_id',
        'google_drive_connected'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_drive_token',
        'google_drive_refresh_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_drive_connected' => 'boolean'
        ];
    }

    public function connectGoogleDrive($token, $refreshToken)
    {
        $this->google_drive_token = $token;
        $this->google_drive_refresh_token = $refreshToken;
        $this->google_drive_connected = true;
        $this->save();
    }

    public function disconnectGoogleDrive()
    {
        $this->google_drive_token = null;
        $this->google_drive_refresh_token = null;
        $this->google_drive_folder_id = null;
        $this->google_drive_connected = false;
        $this->save();
    }

    public function setGoogleDriveFolderId($folderId)
    {
        $this->google_drive_folder_id = $folderId;
        $this->save();
    }
}
