<?php

namespace Lullabot\Mpx\Tests\Unit;

use Lullabot\Mpx\Tests\JsonResponse;
use Lullabot\Mpx\Token;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Lullabot\Mpx\Token
 */
class TokenTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::getValue
     * @covers ::getLifetime
     * @covers ::getExpiration
     */
    public function testToken()
    {
        $time = time();
        $token = new Token('https://example.com/idm/data/User/mpx/123456', 'value', 30);
        $this->assertSame('value', (string) $token);
        $this->assertSame('value', $token->getValue());
        $this->assertSame(30, $token->getLifetime());
        $this->assertSame($time + 30, $token->getExpiration());
    }

    /**
     * @covers ::__construct
     * @covers ::isValid
     */
    public function testTokenExpiration()
    {
        // We use a serialized token as a fixture as we don't allow constructing
        // of expired tokens.
        $token = include __DIR__.'/../../fixtures/ExpiredToken.php';
        $this->assertFalse($token->isValid());
        $this->assertFalse($token->isValid(30));
        $this->assertFalse($token->isValid(60));

        // Test that a token expires after passing time.
        $token = new Token('https://example.com/idm/data/User/mpx/123456', 'value', 1);
        $this->assertTrue($token->isValid());
        sleep(1);
        $this->assertFalse($token->isValid());

        $token = new Token('https://identity.auth.theplatform.com/idm/data/User/mpx/2685072', 'value', 59);
        $this->assertTrue($token->isValid());
        $this->assertTrue($token->isValid(30));
        $this->assertFalse($token->isValid(60));
    }

    /**
     * Test an expiration that will never pass.
     *
     * @covers ::__construct
     */
    public function testInvalidExpiration()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$lifetime must be greater than zero.');
        new Token('https://identity.auth.theplatform.com/idm/data/User/mpx/2685072', 'value', 0);
    }

    /**
     * Test getting the full User ID.
     *
     * @covers ::__construct
     * @covers ::getUserId
     */
    public function testGetUserId()
    {
        $userId = 'https://identity.auth.theplatform.com/idm/data/User/mpx/2685072';
        $token = new Token($userId, 'value', 59);
        $this->assertEquals($userId, $token->getUserId());
    }

    /**
     * Test that created is a timestamp.
     *
     * @covers ::getCreated
     */
    public function testCreated()
    {
        $token = new Token('https://identity.auth.theplatform.com/idm/data/User/mpx/2685072', 'value', 59);
        $this->assertInternalType('integer', $token->getCreated());
    }

    /**
     * Test parsing a response array into a Token.
     *
     * @covers ::fromResponse
     */
    public function testFromResponse()
    {
        $response = new JsonResponse(200, [], 'signin-success.json');
        $token = Token::fromResponse(json_decode($response->getBody(), true));
        $this->assertEquals('TOKEN-VALUE', $token->getValue());
        $this->assertEquals(14400, $token->getLifetime());
        $this->assertEquals('https://identity.auth.theplatform.com/idm/data/User/mpx/1', $token->getUserId());
    }
}
