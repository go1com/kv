<?php

namespace go1\kv\tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use go1\kv\KV;
use PHPUnit\Framework\TestCase;

class KeyValueTest extends TestCase
{
    /** @var  Connection */
    private $read;

    /** @var  Connection */
    private $write;

    /** @var  KV */
    private $kv;

    public function setUp()
    {
        $this->write = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->read = &$this->write;

        $this->kv = new KV($this->read, $this->write);
        $this->kv->install();
    }

    public function testSave()
    {
        $result = $this->kv->save($k = 'foo', $v = 'bar');

        $this->assertEquals(1, $result);
        $this->assertTrue($this->kv->has('foo'));
        $this->assertEquals($v, $this->kv->fetch($k));

        return $k;
    }

    public function testSaveEmpty()
    {
        $result = $this->kv->save($k = 'foo', $v = '');

        $this->assertEquals(1, $result);
        $this->assertTrue($this->kv->has('foo'));
        $this->assertEquals($v, $this->kv->fetch($k));
    }

    /**
     * @expectedException \go1\kv\NotFoundException
     */
    public function testFetchBadKey()
    {
        $this->kv->fetch('invalid-key');
    }

    public function testSaveJson()
    {
        $this->kv->save('foo_object', $value = (object) ['value' => 'VALUE']);
        $this->assertEquals($value, $this->kv->fetch('foo_object'));

        $this->kv->save('foo_array', $value = ['some' => 'value']);
        $this->assertEquals($value, $this->kv->fetch('foo_array'));
    }

    /**
     * @depends testSave
     */
    public function testDelete($k)
    {
        $this->kv->delete($k);
        $this->assertFalse($this->kv->has($k));
    }
}
