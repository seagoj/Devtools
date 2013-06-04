<?php

/**
 * @covers Devtools\Model
 **/
class ModelTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     * @covers Devtools\Model::__construct
     **/
    public function testRedisDefaultConnection()
    {
        $default = new \Devtools\Model();

        $this->assertInstanceOf("Devtools\Model", $default);
        $this->assertTrue($default->connected);
    }

    /**
     * @covers Devtools\Model::__construct
     **/
    public function testRedisCustomConnection()
    {
        $options = [
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379
        ];

        $custom = new \Devtools\Model($options);
        
        $this->assertInstanceOf("Devtools\Model", $custom);
        $this->assertTrue($custom->connected);
    }

    /**
     * @covers Devtools\Model::__construct
     * @covers Devtools\Model::connect
     **/
    public function testRedisConnection()
    {
        $options = ['connect' => false];

        $model = new \Devtools\Model($options);

        $this->assertInstanceOf("Devtools\Model", $model);
        $this->assertFalse($model->connected);
        $model->connect();
        $this->assertTrue($model->connected);
    }

    /**
     * @covers Devtools\Model::set
     * @covers Devtools\Model::get
     **/
    public function testRedisSetGet()
    {
        $model = new \Devtools\Model();

        $this->assertTrue($model->set('Method', __METHOD__));
        $this->assertEquals($model->get('Method'), __METHOD__);
    }

    /**
     * @covers Devtools\Model::set
     * @covers Devtools\Model::get
     **/
    public function testRedisHashSetGet()
    {
        $model = new \Devtools\Model();

        $this->assertTrue($model->set('Method', __METHOD__, __CLASS__));
        $this->assertEquals(__METHOD__, $model->get('Method', __CLASS__));
    }

    /**
     * @covers Devtools\Model::expire
     **/
    public function testRedisExpire()
    {
        $model = new \Devtools\Model();

        $model->set('Method', __METHOD__);
        $this->assertTrue($model->expire('Method', 100));
    }

    /**
     * @covers Devtools\Model::sanitize
     **/
    public function testSanitize()
    {
        $model = new Devtools\Model();
        
        $expected = [
            'html' => [
                'input' => '<body>test</body>',
                'output' => '&lt;body&gt;test&lt;/body&gt;'
            ],
            'shellcmd' => [
                'input' => 'ls -al',
                'output' => escapeshellcmd('ls -al')
            ],
            'shellarg' => [
                'input' => 'ls -al',
                'output' => escapeshellarg('ls -al')
            ]
        ];

        foreach($expected as $type => $options) {
            $this->assertEquals(
                $options['output'], 
                $model->sanitize($options['input'], $type)
            );
        }
    }

    /**
     * @covers Devtools\Model::validateConfig
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    invalid is not a supported datastore type
     **/
    public function testValidateConfigInvalid()
    {
        new \Devtools\Model(['type' => 'invalid']);
    }

    /**
     * @covers Devtools\Model::checkConnection
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Connection is not established
     **/
    public function testCheckConnectionInvalid()
    {
        $model = new \Devtools\Model(['connect' => false]);
        $model->set('key', 'value');
    }
}
