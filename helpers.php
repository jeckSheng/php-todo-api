<?php


if (!function_exists('env')) {
    /**
     * 获取 .env 环境变量
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === false || $value === null) {
            return $default;
        }

        // 处理布尔值：'true' -> true, 'false' -> false
        if (is_string($value)) {
            if (strtolower($value) === 'true') {
                return true;
            }
            if (strtolower($value) === 'false') {
                return false;
            }
            if (strtolower($value) === 'null') {
                return null;
            }
        }

        return $value;
    }
}

if (!function_exists('json_response')) {
    /**
     * 返回 JSON 响应
     */
    function json_response(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('success_return')) {
    function success_return(array $data = [], string $message = 'success')
    {
        json_response([
            'code' => 0,
            'msg' => $message,
            'data' => $data,
        ]);
    }
}

if (!function_exists('error_return')) {
    function error_return(string $message = 'error', int $code = -1)
    {
        json_response([
            'code' => $code,
            'msg' => $message,
            'data' => [],
        ]);
    }
}

if (!function_exists('get_all_headers')) {
    /**
     * 兼容 Apache/Nginx/FPM 获取请求头
     */
    function get_all_headers_polyfill(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if (!function_exists('dd')) {
    function dd($var, $echo = true, $label = null, $strict = true)
    {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo ($output);
            return null;
        } else
            return $output;
    }
}

if (!function_exists('get_request_data')) {
    /**
     * 获取请求数据（兼容 JSON、form-data、x-www-form-urlencoded）
     *
     * @return array|null
     */
    function get_request_data()
    {
        // 1. 先尝试从 $_POST 获取（form-data 或 x-www-form-urlencoded 的 POST 请求）
        if (!empty($_POST)) {
            return $_POST;
        }

        // 2. 尝试从 php://input 读取数据
        $input = file_get_contents('php://input');
        if ($input) {
            // 先检查是否是 multipart/form-data
            if (strpos($input, 'Content-Disposition: form-data') !== false) {
                return parse_multipart_form_data($input);
            }

            // 尝试 JSON 解析
            $data = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }

            // 尝试解析 x-www-form-urlencoded 格式（PUT/DELETE 请求）
            parse_str($input, $parsed);
            if (!empty($parsed)) {
                return $parsed;
            }
        }

        return null;
    }
}

if (!function_exists('parse_multipart_form_data')) {
    /**
     * 解析 multipart/form-data 格式的数据
     *
     * @param string $input
     * @return array
     */
    function parse_multipart_form_data($input)
    {
        $data = [];

        // 提取 boundary
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!preg_match('/boundary="?([^";\s]+)"?/i', $contentType, $matches)) {
            return $data;
        }

        $boundary = $matches[1];

        // 分割各个部分
        $blocks = preg_split('/-+' . preg_quote($boundary, '/') . '/s', $input);

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block) || $block === '--' || strpos($block, 'Content-Disposition') === false) {
                continue;
            }

            // 分离头部和内容
            if (strpos($block, "\r\n\r\n") !== false) {
                list($headers, $body) = explode("\r\n\r\n", $block, 2);
                $body = rtrim($body, "\r\n");

                // 提取 name
                if (preg_match('/name="([^"]*)"/i', $headers, $nameMatches)) {
                    $name = $nameMatches[1];
                    $data[$name] = $body;
                }
            }
        }

        return $data;
    }
}

