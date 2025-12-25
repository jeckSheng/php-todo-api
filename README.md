# 项目概述

这是一个轻量级的 PHP RESTful API 项目，提供任务管理功能（Todo API）。采用无框架的简洁设计，使用 PHP + MySQL + JWT 实现用户认证和任务CRUD操作。

## 环境配置

### 必需的 .env 配置
项目依赖以下环境变量（复制 .env 配置）：
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - MySQL数据库连接
- `DB_PREFIX` - 数据表前缀（默认 do_）
- `JWT_SECRET` - JWT签名密钥（生产环境必须修改）
- `JWT_TTL` - Token有效期（秒，默认604800即7天）

### 开发环境访问
- php >= 8.1
- 数据库服务：`mysql:3306`

## 核心架构

### 请求处理流程
1. 所有请求入口：`index.php`
2. `bootstrap.php` 负责启动：
   - Composer自动加载
   - 加载 .env 环境变量（vlucas/phpdotenv）
   - 初始化 PDO 数据库连接（`config/database.php`）
   - 加载辅助函数（`helpers.php`）
   - 加载认证中间件（`middleware/Auth.php`）

### 简易路由系统
路由在 `index.php` 中通过 `if` 条件判断实现：
- `POST /register` - 用户注册
- `POST /login` - 用户登录（返回JWT token）
- `GET /tasks/list` - 获取任务列表（需认证）
- `POST /tasks/create` - 创建任务（需认证）
- `PUT /tasks/update` - 更新任务（需认证）
- `DELETE /tasks/delete` - 删除任务（需认证）

### 目录结构
```
├── index.php          # 入口文件 + 路由定义
├── bootstrap.php      # 启动引导
├── helpers.php        # 全局辅助函数
├── config/
│   └── database.php   # PDO连接配置（使用全局 $pdo）
├── models/
│   ├── UserModel.php  # 用户模型（注册、登录）
│   └── TaskModel.php  # 任务模型（CRUD、分页查询）
├── middleware/
│   └── Auth.php       # JWT认证中间件
└── database.sql       # 数据库初始化SQL
```

### PSR-4自动加载
Composer配置的命名空间映射：
- `App\Models\` → `models/`
- `App\Middleware\` → `middleware/`
- `App\Controllers\` → `controllers/`（预留）

## 数据库设计

### 表结构
- **do_users**：用户表（id, email, password, created_at, updated_at）
- **do_tasks**：任务表（id, user_id, title, description, status, created_at, updated_at）

### 任务状态常量（TaskModel）
```php
STATUS_WAIT = 0      // 待处理
STATUS_DOING = 1     // 处理中
STATUS_TEST = 2      // 测试中
STATUS_DONE = 3      // 已完成
STATUS_CANCEL = 4    // 已取消
```

## 认证机制

### JWT Token流程
1. 登录成功后返回 token：`Auth::generateToken($userId)`
2. 后续请求需在 Header 携带：`Authorization: Bearer <token>`
3. 受保护路由调用：`Auth::requireAuth()` 返回用户ID
4. Token验证失败自动返回 401 错误

### 密码处理
- 注册时使用 `password_hash($password, PASSWORD_DEFAULT)` 加密
- 登录时使用 `password_verify()` 对比

## 辅助函数（helpers.php）

- `env($key, $default)` - 获取环境变量（支持布尔值转换）
- `success_return($data, $message)` - 返回成功JSON（code=0）
- `error_return($message, $code)` - 返回错误JSON
- `get_all_headers_polyfill()` - 兼容性获取请求头

## 常用开发命令

### Composer操作
```bash
# 安装依赖
composer install

# 自动加载优化
composer dump-autoload -o
```


## API响应格式

统一JSON格式：
```json
{
  "code": 0,        // 0=成功，非0=失败
  "msg": "message",
  "data": {}        // 响应数据
}
```

## CORS配置

当前允许所有来源跨域（index.php:7-9），生产环境需限制。
