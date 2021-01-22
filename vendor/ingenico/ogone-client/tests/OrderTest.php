<?php

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testUnknownCurrency()
    {
        $this->expectException(InvalidArgumentException::class);
        $order = new \IngenicoClient\Order();
        $testOrder = $order->setCurrency('UKN');
    }

    public function testWrongAmountFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $order = new \IngenicoClient\Order();
        $testOrder = $order->setAmount(12.33);
    }

    public function testToLongOrderId()
    {
        $this->expectException(InvalidArgumentException::class);
        $order = new \IngenicoClient\Order();
        $testOrder = $order->setOrderid('2349232349023942934902349239049023490293049023490');
    }

    public function testSpecialCharactersInOrderID()
    {
        $this->expectException(InvalidArgumentException::class);
        $order = new \IngenicoClient\Order();
        $testOrder = $order->setOrderid('123++');
    }
}