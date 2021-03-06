<?php

namespace Doctrine\Tests\DBAL\Types;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Tests\DbalTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use function base64_encode;
use function fopen;
use function json_encode;

class JsonArrayTest extends DbalTestCase
{
    /** @var AbstractPlatform|MockObject */
    protected $platform;

    /** @var JsonArrayType */
    protected $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->type     = Type::getType('json_array');
    }

    public function testReturnsBindingType()
    {
        self::assertSame(ParameterType::STRING, $this->type->getBindingType());
    }

    public function testReturnsName()
    {
        self::assertSame(Types::JSON_ARRAY, $this->type->getName());
    }

    public function testReturnsSQLDeclaration()
    {
        $this->platform->expects($this->once())
            ->method('getJsonTypeDeclarationSQL')
            ->willReturn('TEST_JSON');

        self::assertSame('TEST_JSON', $this->type->getSQLDeclaration([], $this->platform));
    }

    public function testJsonNullConvertsToPHPValue()
    {
        self::assertSame([], $this->type->convertToPHPValue(null, $this->platform));
    }

    public function testJsonEmptyStringConvertsToPHPValue()
    {
        self::assertSame([], $this->type->convertToPHPValue('', $this->platform));
    }

    public function testJsonStringConvertsToPHPValue()
    {
        $value         = ['foo' => 'bar', 'bar' => 'foo'];
        $databaseValue = json_encode($value);
        $phpValue      = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertEquals($value, $phpValue);
    }

    public function testJsonResourceConvertsToPHPValue()
    {
        $value         = ['foo' => 'bar', 'bar' => 'foo'];
        $databaseValue = fopen('data://text/plain;base64,' . base64_encode(json_encode($value)), 'r');
        $phpValue      = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertSame($value, $phpValue);
    }

    public function testRequiresSQLCommentHint()
    {
        self::assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
