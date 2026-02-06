<?php

namespace App\RequestValidation;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * 用户注册/登录验证规则
 */
class RegisterRequest
{
    /**
     * 注册验证规则
     */
    public static function rules(): array
    {
        return [
            'email' => [
                new Assert\NotBlank(['message' => '邮箱不能为空']),
                new Assert\Email(['message' => '邮箱格式不正确']),
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => '邮箱长度不能超过100个字符'
                ])
            ],
            'password' => [
                new Assert\NotBlank(['message' => '密码不能为空']),
                new Assert\Length([
                    'min' => 6,
                    'max' => 25,
                    'minMessage' => '密码长度不能小于6位',
                    'maxMessage' => '密码长度不能超过25位'
                ])
            ]
        ];
    }

    /**
     * 登录验证规则（与注册相同）
     */
    public static function loginRules(): array
    {
        return self::rules();
    }
}
