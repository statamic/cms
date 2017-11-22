<?php namespace Tests;

use Statamic\API\User;

class UserTest extends TestCase
{
    /** @var \Statamic\Contracts\Data\Users\User */
    private $user;

    public function setUp()
    {
        parent::setUp();

        $attributes = [
            'password' => 'test',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'id' => '123'
        ];

        $this->user = User::create()->username('john')->with($attributes)->get();
    }

    public function testGetsData()
    {
        $expected = [
            'password' => 'test',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'id' => '123'
        ];

        $this->assertEquals($expected, $this->user->data());
    }

//    public function testSetsDataAndEncryptsPassword()
//    {
//        $this->user->setData([
//            'username' => 'john',
//            'password' => 'test',
//            'foo' => 'bar'
//        ]);
//
//        $this->assertArraySubset(['username' => 'john', 'foo' => 'bar'], $this->user->getData());
//        $this->assertArrayNotHasKey('password', $this->user->getData());
//        $this->assertArrayHasKey('password_hash', $this->user->getData());
//    }

    public function testGetsId()
    {
        $this->assertEquals('123', $this->user->id());
    }

    public function testGetsUsername()
    {
        $this->assertEquals('john', $this->user->username());
    }

//    public function testGetsPassword()
//    {
//        // an unencrypted password will return false
//        $this->assertFalse($this->user->getPassword());
//
//        $this->user->setData(['password' => 'test']);
//
//        $this->assertEquals('test', $this->user->getPassword());
//    }

//    public function testChecksForEncryptedPassword()
//    {
//        $this->assertFalse($this->user->isEncrypted());
//
//        $this->user->encryptPassword();
//
//        $this->assertTrue($this->user->isEncrypted());
//    }

//    public function testEncryptsPassword()
//    {
//        $this->assertFalse($this->user->isEncrypted());
//
//        $this->user->encryptPassword();
//
//        $this->assertTrue($this->user->isEncrypted());
//    }

    public function testGetsPath()
    {
        $this->assertEquals('john.yaml', $this->user->path());
    }
}
