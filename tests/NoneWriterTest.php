<?php namespace Zipzoft\HttpLogger\Test;

use Zipzoft\HttpLogger\Manager;
use Zipzoft\HttpLogger\NoneWriter;

class NoneWriterTest extends TestCase
{

    public function testInstanceShouldBeNoneWriter()
    {
        $this->assertInstanceOf(NoneWriter::class, (new Manager($this->app))->driver());
    }
}

