<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function PHPUnit\Framework\containsEqual;
use function PHPUnit\Framework\containsOnly;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class PostControllerTest extends WebTestCase
{
    public function testCreateGiftcard()
    {
        $client = static::createClient();

        $body = [
            "type" => "DIGITAL",
            "currency" => [
                "amount" => 51,
                "currency" => "USD"
            ],
            "isValid" => true
        ];
        $body = json_encode($body);
        $client->request('POST', '/giftcard/create', [], [], [], $body);
        $response = $client->getResponse();
        #print_r($response->getContent());
        $id = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        return $id;
    }
    /**
     * @depends testCreateGiftcard
     */
    public function testSearchGiftcard($id): void
    {
        $client = static::createClient();
        $id = json_decode($id, 1);
        $client->request('GET', "/giftcard/search/$id[id]");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
    /**
     * @depends testCreateGiftcard
     */
    public function testInvalidateGiftcard($id): void
    {
        $array = [];
        $id = json_decode($id, 1);
        #var_dump($id);
        $client = static::createClient();
        $client->request('PATCH', "/giftcard/invalidate/$id[id]");
        $response = $client->getResponse();
        $valid = false;
        $valid = json_encode($valid);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $array = json_decode($content, true);
        #var_dump($array);
        $newArray = $array["isValid"];
        $validarray = json_encode($newArray);
        #print_r($valid);
        $this->assertJsonStringEqualsJsonString($valid, $validarray);
        $this->assertJson($response->getContent());
    }
    /**
     * @depends testCreateGiftcard
     */
    public function testRedeemGiftcard($id): void
    {
        $array = [];
        $id = json_decode($id, 1);
        #var_dump($id);
        $client = static::createClient();
        $body = [
            "amount" => 10
        ];
        $body = json_encode($body);
        $client->request('POST', "/giftcard/redeem/$id[id]", [], [], [], $body);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $array = json_decode($content, true);

        $body = json_decode($body, true);
        $newAmount = $array['currency']['amount'] - $body['amount'];
        $newAmount = json_encode($newAmount);
        $oldAmount = json_encode($array['currency']['amount']);
        print($newAmount);
        $this->assertJsonStringNotEqualsJsonString($newAmount, $oldAmount);
        $this->assertJson($response->getContent());
    }
}
