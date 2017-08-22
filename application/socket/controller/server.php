<?php
require_once __DIR__.'/Workerman/Autoloader.php';
use Workerman\Worker;
$worker = new Worker('websocket://0.0.0.0:443');
// Workerman 启动的回调,这里传递的是Worker对象
$worker->onWorkerStart = function ($worker){
    echo "onWorkerStart success";
};
// 连接回调
$worker->onConnect = function ($connection){
//    // IP 白名单验证
//    if($connection->getRemoteIP != '127.0.0.1'){
//        $connection->close("IP Address Forbidden");
//    }
    $connection_baidu = new \Workerman\Connection\AsyncTcpConnection('tcp://www.baidu.com:443');
    // 百度的数据发送给浏览器。返回数据后,使用的数据要use 进来，
    $connection_baidu->onMessage = function ($connection_baidu,$data) use ($connection){
        $connection->send($data);
    };
    // 浏览器接受的数据发送给百度
    $connection->onMessage = function ($connection,$data) use ($connection_baidu){
        $connection_baidu->send($data);
    };
    $connection_baidu->connect();
};
// 接受发送消息
$worker->onMessage = function ($conn,$data){
    $conn->send("Hello World");
};
// 关闭连接
$worker->onClose = function ($connection){
    echo "connection close \n";
};

//Workerman 停止回调
$worker->onWorkerStop = function ($worker){
    echo "onWorkerStop success";
};
$worker::runAll();