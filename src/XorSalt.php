<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 8:54
 *
 */

namespace Badtomcat\Encrypter;


class XorSalt
{
    protected $ascii_key = 0x7e;

    public function encode($a, $salt)
    {
        /**
         * 整个密文分2部分
         * 头部
         *      第1个字节,全文异或
         *      第2个字节,版本号
         *      第3个字节,正文长度
         *      第4个字节,保留
         *
         * 正文
         *
         * */
        //1.生成1-16个随机密钥
        $this->ascii_key = rand(0, 255);     //全文异或
        $rawData = str_split($a);
        $datalen = count($rawData);         //正文长度
        $key = [];

        $key_len = strlen($salt);         //密钥长度
        for ($i = 0; $i < $key_len; $i++) {
            $key[] = ord(substr($salt, $i, 1));
        }

        //2.把数据变成ASCII数组
        $arr = array();                     //正文数据
        foreach ($rawData as $char) {
            $arr[] = ord($char);
        }


        //3.算出KEY长度，数组总长度
        $totalen = 4 + $datalen;                   //头部长度 + 密钥长度 + 正文长度
        $padding = ceil($totalen / 4) * 4 - $totalen;   //8位数据对齐需要PADDING的长度

        //4.把密钥和数据异或
        $i = 0;
        foreach ($arr as $k => $char) {
            $arr[$k] = $char ^ $key[$i % $key_len];
            $i++;
        }

//        print "data len: $datalen\n";
//        print "ascii_key: ".($this->ascii_key ^ $datalen)."\n";

        //5.填充数据,HEAD部分
        $head = [];
        $head[] = $this->ascii_key;
        $head[] = 0x2;
        $head[] = $datalen;
        $head[] = rand(0, 255);
        $data = array_merge($head, $arr);

        //6.对齐数组，按8位
        for ($i = 0; $i < $padding; $i++) {
            $data[] = rand(0, 255);
        }

        //7.从INDEX 1 开始进行整个数组和ascii_key异或,得到的数组
        $arr = [];
        foreach ($data as $i => $char) {
            $arr[] = str_pad(dechex($i == 0 ? $char : $char ^ $this->ascii_key), 2, "0", STR_PAD_LEFT) . "";
        }

        return join("", $arr);
    }

    public function decode($string_hex, $salt)
    {
        $data = str_split($string_hex, 2);

        $key = [];

        $key_len = strlen($salt);         //密钥长度
        for ($i = 0; $i < $key_len; $i++) {
            $key[] = ord(substr($salt, $i, 1));
        }


        //先进行整个数组和第一个元素异或,得到的数组
        $ascii_key = $data[0];
        $data = array_map(function ($code) use ($ascii_key) {
            return hexdec($code) ^ hexdec($ascii_key);
        }, $data);


        $arr = array_slice($data, 4, $data[2]);

        $ret = '';
        for ($i = 0; $i < count($arr); $i++) {
            $ret .= chr(($arr[$i] ^ $key[$i % count($key)]));
        }
        return $ret;
    }

}