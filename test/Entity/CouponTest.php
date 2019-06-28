<?php

namespace CultuurNet\ProjectAanvraag\Entity;

/**
 * Tests the Coupon entity.
 */
class CouponTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $coupon = new Coupon();
        $coupon->setCode('my-code');
        $this->assertEquals('my-code', $coupon->getCode());

        $coupon->setUsed(false);
        $this->assertEquals(false, $coupon->isUsed());
    }
}
