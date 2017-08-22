<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan
 * |  Date: 2017/2/20
 * |  Time: 8:36
 * |  Mail: Overcome.wan@Gmail.com
 * |  Created by PhpStorm. http://115.29.8.55
 * '-------------------------------------------------------------------*/

namespace app\socket\controller;

use redis\BaseRedis;

use think\Log;
use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:12358';

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        while (true) {
            $this->broadcast($connection,$data);
            sleep(6);
        }
    }

    /**
     * 发送消息 $connections
     * @param $msg
     */
    public function broadcast($connection,$data)
    {
        $arrData = json_decode($data);
        $res = $this->redisLogin($arrData);
        Log::info("发送的信息为 : " . $res);
        $sendData = json_encode(['data' => $res, 'errcode' => 0, 'errmsg' => $data]);
        $connection->send($sendData);
    }
    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        // 向当前client_id发送数据
//        echo "当前连接的IP地址：" . $connection->getRemoteIP();
        // 添加一个定时器
        Timer::add(10, function ($worker) use ($connection) {
            if (!isset($connection->name)) {
                return $connection->close("auth fail and close 2");
            }
        }, null, false);
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        echo $connection->id . " : disconnect \r\n";
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        // 进程启动后设置一个每秒运行一次的定时器
        Timer::add(1, function () use ($worker) {
            $time_now = time();
            foreach ($worker->connections as $connection) {
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                if (empty($connection->lastMessageTime)) {
                    $connection->lastMessageTime = $time_now;
                    continue;
                }
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $connection->lastMessageTime > 10) {
                    $connection->close();
                }
            }
        });
        echo $worker->id . "\r\n";
    }

    public function redisLogin($data)
    {
        $redis = BaseRedis::Instance();
        $redis->connect("122.224.187.162",63700);
        $redis->auth("jNAH2AuKDV8FgqrAgcS4tLdS7ZERAqa5twIpJuLOQvJc+mTTX3tzw5CFCzUEjUpNVYmfUQvhc37h2AsCpbbTdazXEAxCNzertPNecJZxHv0=");
        //通过索引获取列表中的元素
        $need = $redis->lIndex("REDIS_MEMORY_INFO:001",-1);
        return $need;
    }

}


