<?php

use PHPUnit\Framework\TestCase;

class DirectLinkTest extends TestCase
{
    public function testDirectLinkPaymentIsSuccessful()
    {
        $configuration = $this->getConfiguration();
        $urls = [
            'accept' => 'http://example.com',
            'exception' => 'http://example.com',
            'back' => 'http://example.com',
            'cancel' => 'http://example.com'
        ];
        $directLink = new \IngenicoClient\DirectLink();
        $alias = new \Ogone\DirectLink\Alias('test');
        $order = new \IngenicoClient\Order();
        $order->setOrderid(123);
        $order->setAmount(1000);
        $order->setCurrency('EUR');
        $directLinkResponse = $directLink->createDirectLinkRequest(
            $configuration,
            $order,
            $alias,
            $urls
        );

        $this->assertTrue($directLinkResponse->isSuccessful());
    }

    public function testCreateRefund()
    {
        $configuration = $this->getConfiguration();
        $directLink = new \IngenicoClient\DirectLink();
        $directLinkResponse = $directLink->createRefund($configuration, '200008999', null, 1000);
        $this->assertInstanceOf('Ogone\\DirectLink\\DirectLinkMaintenanceResponse', $directLinkResponse);
    }

    public function testCreateCapture()
    {
        $configuration = $this->getConfiguration();
        $directLink = new \IngenicoClient\DirectLink();
        $directLinkResponse = $directLink->createCapture($configuration, '200008999', null, null);
        $this->assertInstanceOf('Ogone\\DirectLink\\DirectLinkMaintenanceResponse', $directLinkResponse);
    }

    public function testCreateVoid()
    {
        $configuration = $this->getConfiguration();
        $directLink = new \IngenicoClient\DirectLink();
        $directLinkResponse = $directLink->createVoid($configuration, '200008999', null);
        $this->assertInstanceOf('Ogone\\DirectLink\\DirectLinkMaintenanceResponse', $directLinkResponse);
    }

    public function getConfiguration()
    {
        $configuration = new \IngenicoClient\Configuration(
            PSPID,
            USER,
            PASSWORD,
            PASSPHRASE,
            'sha512'
        );

        return $configuration;
    }

}
