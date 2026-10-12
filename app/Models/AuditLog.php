<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'target_type',
        'target_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper cepat untuk mencatat audit log
     */
    public static function record(string $action, ?string $targetType = null, ?int $targetId = null, $details = null): self
    {
        $user = Auth::user();

        if (is_array($details)) {
            $details = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_role' => $user?->role ?? 'system',
            'action' => strtoupper($action),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
        ]);
    }
}
