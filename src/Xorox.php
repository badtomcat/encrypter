<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 8:54
 *
 */

namespace Badtomcat\Encrypter;


class Xorox
{
    protected $ascii_key = 0x7e;
    protected $key_length_key = 0x38;
    protected $data_length_key = 0x67;

    public function encode($a, $hex_prefix = false)
    {
        /**
         * 整个密文分三部分
         * 头部
         *      第1个字节,全文异或
         *      第2个字节,密钥长度异或
         *      第3个字节,正文长度异或
         *      4,5字节是版本号(异或)
         *      6字节 密钥长度
         *      7,8字节正文密钥   (第7个字节 ^ 第3个字节) << 8 + (第8个字节 ^ 第3个字节)
         * 密钥
         *
         * 正文密钥
         *
         **/

        //1.生成1-16个随机密钥
        $this->ascii_key = rand(0, 255);     //全文异或
        $this->key_length_key = rand(0, 255);    //密钥长度异或
        $this->data_length_key = rand(0, 255);     //正文长度异或
        $rawData = str_split($a);
        $datalen = count($rawData);         //正文长度
        $key = [];
        if ($datalen < 8) {
            $key_min_len = 1;
            $l = rand(0, 4);
        } else {
            $key_min_len = 8;
            $l = rand(0, 8);
        }
        $key_len = $key_min_len + $l;         //密钥长度
        for ($i = 0; $i < $key_len; $i++) {
            $key[] = rand(0, 255);           //随机密钥
        }

        //2.把数据变成ASCII数组
        $arr = array();                     //正文数据
        foreach ($rawData as $char) {
            $arr[] = ord($char);
        }


        //3.算出KEY长度，数组总长度
        $totalen = 8 + $key_len + $datalen;                   //头部长度 + 密钥长度 + 正文长度
        $padding = ceil($totalen / 8) * 8 - $totalen;   //8位数据对齐需要PADDING的长度

        //4.把密钥和数据异或
        $i = 0;
        foreach ($arr as $k => $char) {
            $arr[$k] = $char ^ $key[$i % $key_len];
            $i++;
        }

        //5 版本号随机
        $version_key = rand(0, 255);


        //6.填充数据,HEAD部分
        $head = [];
        $head[] = $this->ascii_key;         //第1个字节,全文异或
        $head[] = $this->key_length_key;    //第2个字节,密钥长度异或
        $head[] = $this->data_length_key;   //第3个字节,正文长度异或
        $head[] = $version_key;
        $head[] = $version_key ^ 0x2;                   //4,5字节是版本号(异或)
        $head[] = $key_len ^ $this->key_length_key;     //6字节 密钥长度
        $a = $datalen >> 8;
        $b = $datalen & 0xff;
        $head[] = $a ^ $this->data_length_key;          //(第7个字节 ^ 第3个字节) << 8
        $head[] = $b ^ $this->data_length_key;          //7,8字节正文密钥  (第8个字节 ^ 第3个字节)
        $data = array_merge($head, $key, $arr);

        //7.对齐数组，按8位
        for ($i = 0; $i < $padding; $i++) {
            $data[] = rand(0, 255);
        }

        //8.从INDEX 1 开始进行整个数组和ascii_key异或,得到的数组
        $arr = [];

        foreach ($data as $i => $char) {
            $arr[] = ($hex_prefix ? '0x' : '') . str_pad(dechex($i == 0 ? $char : $char ^ $this->ascii_key), 2, "0", STR_PAD_LEFT) . "";
        }

        if ($hex_prefix) {
            return '[' . join(",", $arr) . ']';
        } else {
            return join("", $arr);
        }
    }

    public function decode($string_hex)
    {
        if (substr($string_hex, 0, 1) == '[') {
            $string_hex = substr($string_hex, 1);
        }
        if (substr($string_hex, -1, 1) == ']') {
            $string_hex = substr($string_hex, 0, -1);
        }
        if (strpos($string_hex, ",") !== false) {
            $data = explode(",", $string_hex);
        } else {
            $data = str_split($string_hex, 2);
        }

        //先进行整个数组和第一个元素异或,得到的数组
        $ascii_key = $data[0];
        $data = array_map(function ($code) use ($ascii_key) {
            return hexdec($code) ^ hexdec($ascii_key);
        }, $data);
        $key = array_slice($data, 8, $data[5] ^ $data[1]);
        $arr = array_slice($data, ($data[5] ^ $data[1]) + 8, (($data[2] ^ $data[6]) << 8) | ($data[7] ^ $data[2]));

        $ret = '';
        for ($i = 0; $i < count($arr); $i++) {
            $ret .= chr(($arr[$i] ^ $key[$i % count($key)]));
        }
        return $ret;
    }

}