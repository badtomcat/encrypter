<?php


class CodeTest extends PHPUnit_Framework_TestCase
{

    public function testUsedef()
    {
    	$test = new \Badtomcat\Encrypter\Xorox();
    	$ret = $test->encode("akdwedie32165!$#@12");
    	print "\n======================================\n";
    	print chunk_split($ret,8);
        print "\n======================================\n";
        $this->assertEquals("akdwedie32165!$#@12",$test->decode($ret));
    }

    public function testBase32()
    {
        $test = new \Badtomcat\Encrypter\Base32();
        $ret = $test->encode("akdwedie32165!$#@12");
        print "\n======================================\n";
        print chunk_split($ret,8);
        print "\n======================================\n";
        $this->assertEquals("akdwedie32165!$#@12",$test->decode($ret));
    }

    public function testXorsalt()
    {
        $test = new \Badtomcat\Encrypter\XorSalt();
        $ret = $test->encode("akdwedie32165!$#@12","salt..");
//        print "\n======================================\n";
//        print chunk_split($ret,8);
//        print "\n======================================\n";
        $this->assertEquals("akdwedie32165!$#@12",$test->decode($ret,"salt.."));
        $this->assertNotEquals("akdwedie32165!$#@12",$test->decode($ret,"salt..."));
    }
}

