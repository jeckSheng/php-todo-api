<?php

// 启动引导文件

// 自动加载 Composer
require_once __DIR__ . '/vendor/autoload.php';

// 只加载一次
static $dotenv_loaded = false;
if (!$dotenv_loaded) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv_loaded = true;
}

// 加载 helpers
require_once __DIR__ . '/helpers.php';

// 加载数据库配置
require_once __DIR__ . '/config/database.php';


require_once __DIR__ . '/middleware/Auth.php';