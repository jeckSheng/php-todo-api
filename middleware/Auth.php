<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $key;
    private function __construct() {
        JWT::$leeway = 60; // 设置 leeway 为 60 秒
    }

    public static function getKey() {
        if (self::$key === null) {
            self::$key = env('JWT_SECRET', 'default-fallback-secret-change-in-production');
        }
        return self::$key;
    }

    public static function generateToken($userId) {
        $payload = [
            'iss' => 'php-todo-api',
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + (int) env('JWT_TTL', 604800) // 默认 7 天
        ];
        return JWT::encode($payload, self::getKey(), 'HS256');
    }

    public static function getUserIdFromToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::getKey(), 'HS256'));
            return $decoded->sub;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function requireAuth() {
        $headers = get_all_headers_polyfill(); // 使用我们的辅助函数
        $authHeader = $headers['Authorization'] ?? '';

        if (strpos($authHeader, 'Bearer ') !== 0) {
            error_return('Missing or invalid token', 401);
        }

        $token = substr($authHeader, 7);
        $userId = self::getUserIdFromToken($token);

        if (!$userId) {
            error_return('Invalid token', 401);
        }

        return $userId;
    }
}