CREATE DATABASE IF NOT EXISTS todo_api;
USE todo_api;

CREATE TABLE do_users (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '主键',
  `email` VARCHAR ( 255 ) NOT NULL UNIQUE COMMENT '邮箱账号',
  `password` VARCHAR ( 255 ) NOT NULL COMMENT '密码',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  UNIQUE KEY unique_email ( `email` ) 
) ENGINE = innodb DEFAULT CHARSET = utf8mb4 COMMENT '用户表';


CREATE TABLE do_tasks (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '主键',
  `user_id` INT NOT NULL COMMENT '用户id',
  `title` VARCHAR ( 255 ) NOT NULL COMMENT '任务title',
  `description` TEXT COMMENT '任务详情',
  `status` TINYINT ( 1 ) DEFAULT 0 COMMENT '0=待处理 1=处理中 2=测试中 3=已完成 4=废弃',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  KEY idx_user_id ( `user_id` ),
UNIQUE KEY title_uid ( `user_id`, `title` ) 
) ENGINE = innodb DEFAULT CHARSET = utf8mb4 COMMENT '任务表';