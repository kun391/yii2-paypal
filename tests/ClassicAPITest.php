<?php

use kun391\paypal\ClassicAPI;

class ClassicAPITest extends PHPUnit_Framework_TestCase
{
    public function testConstructNotIncludeConfig()
    {
        $obj = new ClassicAPI();
        $this->assertInternalType('array', $obj->_credentials);
        $this->assertEquals('nguyentruongthanh.dn-facilitator-1_api1.gmail.com', $obj->_credentials['acct1.UserName']);
        $this->assertEquals('GRHYUV2DJHNBFTAA', $obj->_credentials['acct1.Password']);
        $this->assertTrue(file_exists($obj->pathFileConfig));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage File config does not exist.
     * @expectedExceptionCode 500
     */
    public function testConstructThrowWhenNoConfigFile()
    {
        $obj = new ClassicAPI();
        $obj->pathFileConfig = 'fake.php';
        $obj->setConfig();
    }

    public function testConstructWithConfig()
    {

    }

    public function testGetAccountInfoEmptyParams()
    {
        $obj = new ClassicAPI();
        $accountInfo = $obj->getAccountInfo();
        $this->assertFalse($accountInfo);
    }
}
