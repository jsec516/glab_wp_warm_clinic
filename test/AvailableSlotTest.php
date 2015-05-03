<?php

class AvailableSlotTest extends PHPUnit_Framework_TestCase{
    public function testPushAndPop()
    {
        $this->assertTrue(true);
    }
    
    public function testEqual(){
        $stack = array();
        $this->assertEquals(0, count($stack));
    }
}