<?php

namespace Lullabot\Mpx\Tests\Unit\Exception;

use Lullabot\Mpx\Exception\MpxExceptionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test our common methods between client and server exceptions.
 *
 * @coversDefaultClass \Lullabot\Mpx\Exception\MpxExceptionTrait
 */
class MpxExceptionTraitTest extends TestCase
{
    /**
     * Test basic setters and getters.
     *
     * @covers ::setData
     * @covers ::getTitle
     * @covers ::getDescription
     * @covers ::getCorrelationId
     * @covers ::getServerStackTrace
     * @covers ::getData
     */
    public function testGet()
    {
        $trait = $this->getMockForTrait(MpxExceptionTrait::class);
        $data = [
            'responseCode' => 403,
            'isException' => true,
            'title' => 'Access denied',
            'description' => 'Authentication credentials invalid',
            'correlationId' => 'correlation-id',
            'serverStackTrace' => 'stack-trace',
        ];
        $trait->setData($data);

        $this->assertEquals($data['title'], $trait->getTitle());
        $this->assertEquals($data['description'], $trait->getDescription());
        $this->assertEquals('correlation-id', $trait->getCorrelationId());
        $this->assertEquals('stack-trace', $trait->getServerStackTrace());
        $this->assertEquals($data, $trait->getData());
    }

    /**
     * Test when no correlation ID is set.
     *
     * @covers ::getCorrelationId
     */
    public function testNoCorrelationId()
    {
        $trait = $this->getMockForTrait(MpxExceptionTrait::class);
        $data = [
            'responseCode' => 403,
            'isException' => true,
            'title' => 'Access denied',
            'description' => 'Authentication credentials invalid',
            'serverStackTrace' => 'stack-trace',
        ];
        $trait->setData($data);

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('correlationId is not included in this error.');
        $trait->getCorrelationId();
    }

    /**
     * Test when no server stack trace is set.
     *
     * @covers ::getServerStackTrace
     */
    public function testNoServerStackTrace()
    {
        $trait = $this->getMockForTrait(MpxExceptionTrait::class);
        $data = [
            'responseCode' => 403,
            'isException' => true,
            'title' => 'Access denied',
            'description' => 'Authentication credentials invalid',
        ];
        $trait->setData($data);

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('serverStackTrace is not included in this error.');
        $trait->getServerStackTrace();
    }

    /**
     * Test validating a valid MPX error.
     *
     * @doesNotPerformAssertions
     *
     * @covers ::validateData
     * @doesNotPerformAssertions
     */
    public function testValidateData()
    {
        $data = [
            'responseCode' => 503,
            'isException' => 1,
            'title' => 'the title',
            'description' => 'the description',
        ];
        MpxExceptionTrait::validateData($data);
    }

    /**
     * Test validating an invalid MPX error.
     *
     * @param array  $data The data that is missing a required valid.
     * @param string $key  The key that is missing from $data.
     *
     * @dataProvider validateDataProvider
     *
     * @covers ::validateData
     */
    public function testValidateInvalidData($data, $key)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Required key %s is missing.', $key));
        MpxExceptionTrait::validateData($data);
    }

    /**
     * Data provider for testing error validation.
     *
     * @return array An array with an invalid error and the missing key.
     */
    public function validateDataProvider()
    {
        $required = [
            'responseCode' => 503,
            'isException' => 1,
            'title' => 'the title',
            'description' => 'the description',
        ];
        $data = array_map(function ($value) use (&$required) {
            end($required);
            $key = key($required);
            array_pop($required);

            return [$required, $key];
        }, $required);

        return $data;
    }
}