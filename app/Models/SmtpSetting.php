<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'reply_to_address',
        'reply_to_name',
        'timeout',
        'local_domain',
        'is_active',
    ];

    protected $casts = [
        'port' => 'integer',
        'timeout' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the active SMTP settings.
     *
     * @return SmtpSetting|null
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get the default SMTP settings (first record or create default).
     *
     * @return SmtpSetting
     */
    public static function getDefault()
    {
        return self::first() ?? new self();
    }
}
