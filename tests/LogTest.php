<?php

// Required to test output

/**
 * @covers \Devtools\Log()
 **/
class LogTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function tearDown()
    {
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Devtools\Log', new \Devtools\Log());
    }

    public function testDefaults()
    {
        $log = new \Devtools\Log();
        $this->assertAttributeEquals(
            array('type'=>'file', 'file'=>'Log.log', 'format'=>'tap'),
            'config',
            $log
        );
//        $this->assertTrue(is_file(__CLASS__.'.log'));
//        $this->assertTrue(file_get_contents(__CLASS__.'.log')!=='');
//        unlink(__CLASS__.'.log');
    }

    public function testCustomTypeValid()
    {
        $options = array('type'=>'html');
        $log = new \Devtools\Log($options);
    }

    public function testCustomFileValid()
    {
        $options = array('file'=>__METHOD__.'.log');
        $log = new \Devtools\Log($options);

        $this->assertAttributeEquals(
            array('type'=>'file', 'file'=>__METHOD__.'.log', 'format'=>'tap'),
            'config',
            $log
        );
    }

    public function testWrongType()
    {
        $options = array('type'=>'invalid');
        $this->setExpectedException('InvalidArgumentException');
        $log = new \Devtools\Log($options);
        $log->write("Brokwn");
    }

    public function testFile()
    {
        $options = array('file'=>__METHOD__.'.log');

        $log = new \Devtools\Log($options);
        $log->write('Test');

        $this->assertTrue(is_file($options['file']));
        $this->assertTrue(file_get_contents($options['file'])!=='');
    }

    public function testTapifyTrue()
    {
        $message = "Sample Output";

        $method = new ReflectionMethod('Devtools\Log', 'tapify');
        $method->setAccessible(true);

        $this->assertTrue(false !== strpos($method->invoke(new \Devtools\Log(), $message, true), "ok 1 - $message"));
    }

    public function testStdout()
    {
        $message = __METHOD__;
        $options = array('type'=>'stdout');
        $log = new \Devtools\Log($options);

        ob_start();
        $log->write($message);
        $this->assertEquals($message."\n", ob_get_clean());
    }
}
