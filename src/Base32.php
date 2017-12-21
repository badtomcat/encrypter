<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 13:45
 */
namespace Badtomcat\Encrypter;
class Base32
{
    private $hashtable = "abcdefghjkmnpqrstuwxyz0123456789";

    public function encode($s)
    {
        $i = 0;
        $result = array();
        while ($i < strlen($s)) {
            $ascii = ord($s [$i]);
            $result [] = str_pad(decbin($ascii), 8, "0", STR_PAD_LEFT);
            $i++;
        }
        $_t = str_split(join("", $result), 5);
        $len = count($_t);
        $result = array();
        foreach ($_t as $val) {
            $result [] = $this->hashtable [bindec($val)];
        }
        return strlen($_t [$len - 1]) . join("", $result);
    }

    public function decode($s)
    {
        $i = 1;
        $result = array();
        while ($i < strlen($s)) {
            $cur = strpos($this->hashtable, $s [$i]);
            $result [] = str_pad(decbin($cur), 5, "0", STR_PAD_LEFT);
            $i++;
        }
        $len = count($result);
        $result [$len - 1] = substr($result [$len - 1], 5 - $s [0]);
        $_t = str_split(join("", $result), 8);
        $result = array();
        foreach ($_t as $val) {
            $result [] = chr(bindec($val));
        }
        return join("", $result);
    }
}