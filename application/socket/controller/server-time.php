<?php
/**
 * 定时器学习
 */
require_once __DIR__ . '/Workerman/Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;
// 创建一个容器
$worker = new Worker('websocket://0.0.0.0:12345');
// 连接回调
$worker->onConnect = function ($connection) {
    // 每10s 检查客户端是否有name属性
    Timer::add(10, function () use ($connection) {
        if (!isset($connection->name)) {
            $connection->close("auth timeout and close");
        }
    }, null, false);
};

$worker->onMessage = function ($connection, $data) {
    if (!isset($connection->name)) {
        $data = json_decode($data,true);
        if (!isset($data['name']) || !isset($data['password'])) {
            return $connection->close("auth fail and close");
        }
        // 如果客户端name存在，mysql，这里使用动态给对象赋值属性name,标记该对象已经通过验证
        $connection->name = $data['name'];
        // 广播给所有用户，该用户加入
        return broadcast($connection->name . 'join');
    }
    // 简单的连接器
    return broadcast($connection->name . 'said : ' . $data);
};

function broadcast($msg)
{
    // 引入$worker 对象
    global $worker;
    // $worker->connections 为客户端连接的所有对象
    foreach ($worker->connections as $connection) {
        if (!isset($connection->name)) {
            //没有权限认证的，忽略掉
            continue;
        }
        $connection->send($msg);
    }
}

$worker::runAll();