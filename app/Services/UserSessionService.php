<?php

namespace App\Services;

use App\Models\AccessRegister;
use App\Models\UserSession;
use Illuminate\Http\Request;

class UserSessionService
{
    public static function parseUserAgent(?string $userAgent): array
    {
        $platform = 'Unknown';
        $browser = 'Unknown';
        $device = 'Desktop';

        if (!$userAgent) {
            return compact('platform', 'browser', 'device');
        }

        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        if (preg_match('/edg\//i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/firefox\//i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/chrome\//i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari\//i', $userAgent)) {
            $browser = 'Safari';
        }

        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            $device = 'Mobile';
        }

        return compact('platform', 'browser', 'device');
    }

    public static function recordLogin(AccessRegister $user, Request $request): UserSession
    {
        $parsed = self::parseUserAgent($request->userAgent());
        $sessionId = $request->session()->getId();
        $now = now();

        UserSession::where('access_register_id', $user->id)
            ->whereNull('logged_out_at')
            ->update(['is_current' => false]);

        return UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'access_register_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => $parsed['browser'],
                'platform' => $parsed['platform'],
                'device' => $parsed['device'],
                'is_current' => true,
                'last_activity' => $now,
                'logged_in_at' => $now,
                'logged_out_at' => null,
            ]
        );
    }

    public static function touch(Request $request): void
    {
        if (!$request->user()) {
            return;
        }

        UserSession::where('session_id', $request->session()->getId())
            ->where('access_register_id', $request->user()->id)
            ->whereNull('logged_out_at')
            ->update(['last_activity' => now()]);
    }

    public static function isSessionActive(Request $request): bool
    {
        if (!$request->user()) {
            return true;
        }

        $record = UserSession::where('session_id', $request->session()->getId())
            ->where('access_register_id', $request->user()->id)
            ->first();

        if ($record && $record->logged_out_at !== null) {
            return false;
        }

        if (!$record) {
            self::recordLogin($request->user(), $request);
        } else {
            self::touch($request);
        }

        return true;
    }

    public static function logoutCurrent(Request $request): void
    {
        if (!$request->user()) {
            return;
        }

        UserSession::where('session_id', $request->session()->getId())
            ->where('access_register_id', $request->user()->id)
            ->update([
                'logged_out_at' => now(),
                'is_current' => false,
            ]);
    }

    public static function revoke(UserSession $session): void
    {
        $session->update([
            'logged_out_at' => now(),
            'is_current' => false,
        ]);
    }

    public static function logoutAllForUser(AccessRegister $user): void
    {
        UserSession::where('access_register_id', $user->id)
            ->whereNull('logged_out_at')
            ->update([
                'logged_out_at' => now(),
                'is_current' => false,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function serialize(UserSession $session, string $currentSessionId): array
    {
        return [
            'id' => $session->id,
            'platform' => $session->platform,
            'browser' => $session->browser,
            'device' => $session->device,
            'ip_address' => $session->ip_address,
            'is_current' => $session->session_id === $currentSessionId,
            'last_activity' => $session->last_activity?->toIso8601String(),
            'logged_in_at' => $session->logged_in_at?->toIso8601String(),
        ];
    }
}
