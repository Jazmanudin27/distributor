<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key.
     */
    public static function getVal($key, $default = null)
    {
        $setting = self::find($key);
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key.
     */
    public static function setVal($key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
