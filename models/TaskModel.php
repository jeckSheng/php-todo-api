<?php

namespace App\Models;

class TaskModel
{
    private $pdo;

    const STATUS_WAIT = 0;
    const STATUS_DOING = 1;
    const STATUS_TEST = 2;
    const STATUS_DONE = 3;
    const STATUS_CANCEL = 4;

    const STATUS_MAP = [
        self::STATUS_WAIT => '待处理',
        self::STATUS_DOING => '处理中',
        self::STATUS_TEST => '测试中',
        self::STATUS_DONE => '已完成',
        self::STATUS_CANCEL => '已取消',
    ];

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPageList(array $params = [], int $page = 1, int $pageSize = 15)
    {
        if ($page < 1) {
            $page = 1;
        }

        if ($pageSize < 1 || $pageSize > 100) {
            $pageSize = 15;
        }

        $offset = ($page - 1) * $pageSize;

        $where = [];
        if (!empty($params['status']) && in_array($params['status'], [self::STATUS_WAIT, self::STATUS_DOING, self::STATUS_TEST, self::STATUS_DONE, self::STATUS_CANCEL])) {
            $where[] = "status = :status";
        }
        if (!empty($params['title']) && strlen($params['title']) > 0) {
            $where[] = "title LIKE :title";
        }
        if (!empty($params['user_id']) && $params['user_id']) {
            $where[] = "user_id = :user_id";
        }
        $where = implode(' AND ', $where);


        $sql = "SELECT * FROM do_tasks";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $sql .= " ORDER BY id DESC LIMIT :offset, :pageSize";

        $stmt = $this->pdo->prepare($sql);

        if (!empty($params['status']) && in_array($params['status'], [self::STATUS_WAIT, self::STATUS_DOING, self::STATUS_TEST, self::STATUS_DONE, self::STATUS_CANCEL])) {
            $stmt->bindValue(':status', $params['status'], \PDO::PARAM_INT);
        }
        if (!empty($params['title']) && strlen($params['title']) > 0) {
            $stmt->bindValue(':title', "%{$params['title']}%", \PDO::PARAM_STR);
        }
        if (!empty($params['user_id']) && $params['user_id']) {
            $stmt->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
        }

        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'status' => $item['status'],
                'status_text' => self::STATUS_MAP[$item['status']],
                'user_id' => $item['user_id'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
            ];
        }

        return ['list' => $list, 'total' => $this->getTotalCount($params)];
    }

    public function getTotalCount(array $params = [])
    {
        $where = [];
        if (!empty($params['status']) && in_array($params['status'], [self::STATUS_WAIT, self::STATUS_DOING, self::STATUS_TEST, self::STATUS_DONE, self::STATUS_CANCEL])) {
            $where[] = "status = :status";
        }
        if (!empty($params['title']) && strlen($params['title']) > 0) {
            $where[] = "title LIKE :title";
        }
        if (!empty($params['user_id']) && $params['user_id']) {
            $where[] = "user_id = :user_id";
        }
        $where = implode(' AND ', $where);

        $sql = "SELECT count(*) FROM do_tasks";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $stmt = $this->pdo->prepare($sql);
        if (!empty($params['status']) && in_array($params['status'], [self::STATUS_WAIT, self::STATUS_DOING, self::STATUS_TEST, self::STATUS_DONE, self::STATUS_CANCEL])) {
            $stmt->bindValue(':status', $params['status'], \PDO::PARAM_INT);
        }
        if (!empty($params['title']) && strlen($params['title']) > 0) {
            $stmt->bindValue(':title', "%{$params['title']}%", \PDO::PARAM_STR);
        }
        if (!empty($params['user_id']) && $params['user_id']) {
            $stmt->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function findById(int $id)
    {
        $sql = "SELECT * FROM do_tasks WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();

        if (!$data) {
            return [];
        }
        return [
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'status_text' => self::STATUS_MAP[$data['status']],
            'user_id' => $data['user_id'],
            'created_at' => $data['created_at'] ? date('Y-m-d H:i:s', $data['created_at']) : '',
        ];
    }

    public function create(array $data)
    {
        $required = ['title', 'status', 'user_id'];
        if (array_diff($required, array_keys($data))) {
            throw new \Exception('参数错误');
        }

        if (!in_array($data['status'], [self::STATUS_WAIT, self::STATUS_DOING, self::STATUS_TEST, self::STATUS_DONE, self::STATUS_CANCEL])) {
            throw new \Exception('参数错误');
        }

        if (!is_int($data['user_id'])) {
            throw new \Exception('参数错误');
        }

        $sql = "INSERT INTO do_tasks (`title`, `description`, `status`, `user_id`) VALUES (:title, :description, :status, :user_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':title', $data['title'], \PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', \PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], \PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function update(int $id, int $user_id, array $data)
    {
        if (!in_array($data['status'], [self::STATUS_WAIT, self::STATUS_DOING, self::STATUS_TEST, self::STATUS_DONE, self::STATUS_CANCEL])) {
            throw new \Exception('参数错误');
        }

        $field = ['title', 'description', 'status'];
        $setSql = '';
        foreach ($field as $item) {
            if (isset($data[$item])) {
                $setSql .= ($setSql ? ',' : '') . "`$item` = :$item";
            }
        }

        if (empty($setSql)) {
            throw new \Exception('没有要更新的字段');
        }

        $sql = "UPDATE do_tasks SET $setSql WHERE id = :id and user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
        foreach ($field as $item) {
            if (isset($data[$item])) {
                $stmt->bindValue(":$item", $data[$item], \PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete(int $id)
    {
        $sql = "DELETE FROM do_tasks WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
