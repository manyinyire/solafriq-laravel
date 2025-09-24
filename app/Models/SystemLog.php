<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category',
        'message',
        'level',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The "updated_at" timestamp is not used for logs.
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = ['created_at'];

    /**
     * Scope a query to only include error logs.
     */
    public function scopeErrors($query)
    {
        return $query->where('level', 'ERROR');
    }

    /**
     * Scope a query to only include warning logs.
     */
    public function scopeWarnings($query)
    {
        return $query->where('level', 'WARN');
    }

    /**
     * Scope a query to only include info logs.
     */
    public function scopeInfo($query)
    {
        return $query->where('level', 'INFO');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Create a new log entry.
     */
    public static function log(string $category, string $message, string $level = 'INFO', array $metadata = []): self
    {
        return self::create([
            'category' => $category,
            'message' => $message,
            'level' => $level,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Log an error.
     */
    public static function error(string $category, string $message, array $metadata = []): self
    {
        return self::log($category, $message, 'ERROR', $metadata);
    }

    /**
     * Log a warning.
     */
    public static function warning(string $category, string $message, array $metadata = []): self
    {
        return self::log($category, $message, 'WARN', $metadata);
    }

    /**
     * Log info.
     */
    public static function info(string $category, string $message, array $metadata = []): self
    {
        return self::log($category, $message, 'INFO', $metadata);
    }
}