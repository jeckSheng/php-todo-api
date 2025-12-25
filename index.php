<?php

require_once __DIR__ . '/bootstrap.php';

// 导入命名空间
use App\Models\UserModel;
use App\Models\TaskModel;
use App\Middleware\Auth;

// 允许跨域（保留）
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 获取请求方法
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    success_return();
}

// 获取请求路径
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// 简易路由
// 处理根路径
if ($path === '/') {
    success_return([
        'message' => 'Hello World!',
    ]);
} elseif ($path === '/register' && $method === 'POST') {
    // 处理注册逻辑
    $data = get_request_data();

    if (!$data) {
        error_return('参数错误');
    }
    $email = $data['email'];
    $password = $data['password'];

    $userModel = new UserModel($pdo);
    $user = $userModel->findByEmail($email);

    if ($user) {
        error_return('用户已存在');
    }

    $userModel->create($email, $password);

    success_return([], '注册成功');
} elseif ($path === '/login' && $method === 'POST') {
    // 处理登录逻辑
    $data = get_request_data();

    if (!$data) {
        error_return('参数错误');
    }
    $email = $data['email'];
    $password = $data['password'];

    $userModel = new UserModel($pdo);
    $user = $userModel->findByEmail($email);

    if (!$user) {
        error_return('用户名或密码错误');
    }

    if (!password_verify($password, $user['password'])) {
        error_return('用户名或密码错误');
    }

    $data = [
        'email' => $email,
        'token' => Auth::generateToken($user['id']),
    ];

    success_return($data, '登录成功');
} elseif ($path === '/tasks/list' && $method === 'GET') {
    $userId = Auth::requireAuth();

    $taskModel = new TaskModel($pdo);
    $params = [
        'user_id' => $userId,
    ];

    $title = $_GET['title'] ?? '';
    if ($title) {
        $params['title'] = $title;
    }
    $status = $_GET['status'] ?? '';
    if ($status) {
        $params['status'] = $status;
    }
    $page = $_GET['page'] ?? 1;
    $pageSize = $_GET['page_size'] ?? 15;

    $data = $taskModel->getPageList($params, $page, $pageSize);

    success_return($data);
} elseif ($path === '/tasks/create' && $method === 'POST') {
    $userId = Auth::requireAuth();
    $data = get_request_data();

    if (!$data) {
        error_return('参数错误');
    }

    try {
        $taskModel = new TaskModel($pdo);
        $id = $taskModel->create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);

        success_return(['id' => $id], '创建成功');
    } catch (\Exception $e) {
        error_return($e->getMessage());
    }
} elseif ($path === '/tasks/update' && $method === 'PUT') {
    $userId = Auth::requireAuth();
    $data = get_request_data();

    if (!$data) {
        error_return('参数错误');
    }
    
    try {
        $taskModel = new TaskModel($pdo);
        $taskModel->update($data['id'], $userId, [
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);
        success_return([], '更新成功');
    } catch (\Exception $e) {
        error_return($e->getMessage());
    }
} elseif ($path === '/tasks/delete' && $method === 'DELETE') {
    $userId = Auth::requireAuth();
    $data = get_request_data();

    if (!$data) {
        error_return('参数错误');
    }

    try {
        $taskModel = new TaskModel($pdo);
        $taskModel->delete($data['id']);
        success_return([], '删除成功');
    } catch (\Exception $e) {
        error_return($e->getMessage());
    }
} 

error_return('接口不存在');