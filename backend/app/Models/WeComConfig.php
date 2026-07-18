<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeComConfig extends Model
{
    protected $table = 'we_com_configs';
    protected $fillable = ['name', 'encrypted_webhook', 'enabled', 'last_tested_at', 'last_test_status', 'last_test_message'];
    protected $hidden = ['encrypted_webhook'];
    protected $casts = ['enabled' => 'boolean', 'last_tested_at' => 'datetime'];
}
