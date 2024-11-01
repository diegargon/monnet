<?php

require_once __DIR__ . '/../index.php';

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase {

    public function testHomePage() {
        $url = 'http://127.0.0.1/index.php';
        $response = $this->makeRequest($url);
        $this->assertEquals(200, $response['http_code'], 'HTTP code not 200');
        $this->assertStringContainsString('Bienvenido', $response['body'], 'Wrong body');
    }

    private function makeRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'body' => $body,
            'http_code' => $http_code
        ];
    }

#    public function testGreet() {
#        $this->assertEquals("Hello, World!", greet());
#    }
}
