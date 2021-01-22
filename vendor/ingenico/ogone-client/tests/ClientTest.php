<?php

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testPostRequest()
    {
        $request = new \IngenicoClient\Client();
        $response = $request->post([], 'http://example.com', 'NULL');

        $this->assertNotFalse($response);
    }
}