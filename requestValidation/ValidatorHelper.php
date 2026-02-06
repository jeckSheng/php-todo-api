<?php

namespace App\RequestValidation;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class ValidatorHelper
{
    private static $validator;

    /**
     * 初始化单例验证器
     */
    private static function getValidator()
    {
        if (self::$validator === null) {
            // 创建基础验证器
            self::$validator = Validation::createValidator();
        }
        return self::$validator;
    }

    /**
     * 验证数组数据
     * @param array $data 需要验证的数据 ($_POST 或 json_decode 后的数据)
     * @param array $constraints 验证规则
     * @return array|null 验证通过返回 null，失败返回错误数组
     */
    public static function validate(array $data, array $constraints)
    {
        $validator = self::getValidator();

        // 允许额外的字段（比如请求里带了不需要的参数），且允许字段缺失（如果你在规则里用了 Optional）
        $collectionConstraint = new Assert\Collection([
            'fields' => $constraints,
            'allowExtraFields' => true,
            'allowMissingFields' => true 
        ]);

        $violations = $validator->validate($data, $collectionConstraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                // 处理路径名，去掉方括号
                $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
                $errors[$field] = $violation->getMessage();
            }
            return $errors;
        }

        return null;
    }
}