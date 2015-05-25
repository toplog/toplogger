<?php

use TopLog\Toplogger\Toplogger;
use Mockery as m;

class ToploggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        m::close();
    }

    public function testGetAndSetLogLevels()
    {
        $toplogger = new Toplogger('Test', 'test.log', __DIR__ . '/data/output', 'default', null, null, null, [100, 200]);

        $this->assertEquals([100, 200], $toplogger->getLogLevels());

        $toplogger->setLogLevels([300, 400, 500]);

        $this->assertEquals([300, 400, 500], $toplogger->getLogLevels());

        $toplogger = new Toplogger('Test', 'test.log', __DIR__ . '/data/output', 'production', null, null, null, []);

        $this->assertEquals([200, 400, 550], $toplogger->getLogLevels());

        $toplogger = new Toplogger('Test', 'test.log', __DIR__ . '/data/output', 'production', null, null, null, [123]);

        $this->assertEquals([123], $toplogger->getLogLevels());
    }

    public function testGetAndSetSlackLevels()
    {
        $toplogger = new Toplogger('Test', 'test.log', __DIR__ . '/data/output', 'default', null, null, [100, 200]);

        $this->assertEquals([100, 200], $toplogger->getSlackLevels());

        $toplogger->setSlackLevels([300, 400, 500]);

        $this->assertEquals([300, 400, 500], $toplogger->getSlackLevels());

        $toplogger = new Toplogger('Test', 'test.log', __DIR__ . '/data/output', 'production', null, null, [], null);

        $this->assertEquals([200, 550], $toplogger->getSlackLevels());

        $toplogger = new Toplogger('Test', 'test.log', __DIR__ . '/data/output', 'production', null, null, [123], null);

        $this->assertEquals([123], $toplogger->getSlackLevels());
    }
}
