<?php

namespace App\RequestValidation;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * 任务相关验证规则
 */
class TaskRequest
{
    /**
     * 创建任务验证规则
     */
    public static function createRules(): array
    {
        return [
            'title' => [
                new Assert\NotBlank(['message' => '任务标题不能为空']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => '标题至少2个字符',
                    'maxMessage' => '标题最多100个字符'
                ])
            ],
            'description' => [
                new Assert\Length([
                    'max' => 500,
                    'maxMessage' => '描述最多500个字符'
                ])
            ],
            'status' => [
                new Assert\Choice([
                    'choices' => [0, 1, 2, 3, 4],
                    'message' => '状态值无效（0=待处理, 1=处理中, 2=测试中, 3=已完成, 4=已取消）'
                ])
            ]
        ];
    }

    /**
     * 更新任务验证规则
     */
    public static function updateRules(): array
    {
        return [
            'id' => [
                new Assert\NotBlank(['message' => '任务ID不能为空']),
                new Assert\Type(['type' => 'numeric', 'message' => 'ID必须是数字']),
                new Assert\Positive(['message' => 'ID必须是正数'])
            ],
            'title' => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => '标题至少2个字符',
                    'maxMessage' => '标题最多100个字符'
                ])
            ],
            'description' => [
                new Assert\Length([
                    'max' => 500,
                    'maxMessage' => '描述最多500个字符'
                ])
            ],
            'status' => [
                new Assert\Choice([
                    'choices' => [0, 1, 2, 3, 4],
                    'message' => '状态值无效（0=待处理, 1=处理中, 2=测试中, 3=已完成, 4=已取消）'
                ])
            ]
        ];
    }

    /**
     * 删除任务验证规则
     */
    public static function deleteRules(): array
    {
        return [
            'id' => [
                new Assert\NotBlank(['message' => '任务ID不能为空']),
                new Assert\Type(['type' => 'numeric', 'message' => 'ID必须是数字']),
                new Assert\Positive(['message' => 'ID必须是正数'])
            ]
        ];
    }

    /**
     * 任务列表查询验证规则
     */
    public static function listRules(): array
    {
        return [
            'page' => [
                new Assert\Type(['type' => 'numeric', 'message' => '页码必须是数字']),
                new Assert\Positive(['message' => '页码必须是正数'])
            ],
            'limit' => [
                new Assert\Type(['type' => 'numeric', 'message' => '每页数量必须是数字']),
                new Assert\Range([
                    'min' => 1,
                    'max' => 100,
                    'notInRangeMessage' => '每页数量必须在1到100之间'
                ])
            ],
            'status' => [
                new Assert\Choice([
                    'choices' => [0, 1, 2, 3, 4],
                    'message' => '状态值无效'
                ])
            ]
        ];
    }
}
