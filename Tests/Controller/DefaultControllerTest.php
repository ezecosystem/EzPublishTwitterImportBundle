<?php

namespace xrow\EzPublishTwitterImportBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/xrow');

        $this->assertTrue($crawler->filter('html:contains("Hello Xrow")')->count() > 0);
    }
}
