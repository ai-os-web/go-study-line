<?php

/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/8/28 14:42
 * |  Mail: Overcome.wan@Gmail.com
 * '-------------------------------------------------------------------*/

namespace app\common\controller;

use think\Controller;

class Base extends Controller
{
    // 自定义状态码
    static $return_code = [
        '200' => '操作成功',
        '301' => '网站已被永久移动到新位置',
        '302' => '网站已被临时移动到新位置',
        '401' => '身份验证错误,此页要求授权',
        '403' => '(禁止)服务器拒绝请求',
        '404' => '服务器找不到请求的网页',
        '500' => '服务器遇到错误，无法完成请求',
        // 扩展状态码
        '2001' => '添加成功',
        '5001' => '添加失败',
        '2002' => '更新成功',
        '5002' => '更新失败',
        '2003' => '删除成功',
        '5003' => '删除失败',
        '2004' => '用户账号被禁用'
    ];

    /**
     * json格式返回状态码模板
     * @param string $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function returnCode($code = '', $msg = '', $data = [])
    {
        $return_data = [
            'code' => '500',
            'msg' => '未定义消息',
            'data' => $code == 1001 ? $data : []
        ];
        if (empty($code)) return $return_data;
        $return_data['code'] = $code;
        if (!empty($msg)) {
            $return_data['msg'] = $msg;
        } else if (isset(self::$return_code[$code])) {
            $return_data['msg'] = self::$return_code[$code];
        }
        return json($return_data);
    }
}