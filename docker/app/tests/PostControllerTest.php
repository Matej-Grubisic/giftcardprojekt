<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function PHPUnit\Framework\containsEqual;
use function PHPUnit\Framework\containsOnly;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class PostControllerTest extends WebTestCase
{
    public function testSearchGiftcard(): void
    {
        $client = static::createClient();
        #nemoj hardkodira uvik <<<<<<<<< popravi ovo ispod
        $client->request('GET', '/giftcard/search/d0fea202bab24e60982f');
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        #$responseData = json_decode($response->getContent(), true);
        
    }
    public function testCreateGiftcard(): void
    {
        
        $client = static::createClient();
        
        $body = [
                "type" => "DIGITAL",
                "currency"=>[
                    "amount"=> 51,
                    "currency"=> "USD"
                ],
                "isValid"=> true
            ];
        $body = json_encode($body);
        $client->request('POST', '/giftcard/create', [], [], [], $body);
        $response = $client->getResponse();
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
    public function testInvalidateGiftcard(): void
    {
        $array = [];
        $client = static::createClient();
        $client->request('PATCH', '/giftcard/invalidate');
        $response = $client->getResponse();
        $valid = false;

        $valid = json_encode($valid);
        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $array = json_decode($content, true);
        
        #var_dump($array);
        $validarray = json_encode($array["isValid"]);
        #print_r($valid);
        
        $this->assertJsonStringEqualsJsonString($valid, $validarray);
        $this->assertJson($response->getContent());
    }
    public function testRedeemGiftcard(): void
    {
        $array = [];
        $client = static::createClient();
        $client->request('POST', '/giftcard/redeem');
        $response = $client->getResponse();
        $used = true;

        $used = json_encode($used);
        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $array = json_decode($content, true);
        
        #var_dump($array);
        $usedarray = json_encode($array["isValid"]);
        #print_r($valid);
        
        $this->assertJsonStringEqualsJsonString($used, $usedarray);
        $this->assertJson($response->getContent());
    }
}
