<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Coupon entity.
 */
class CouponTest extends TestCase
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
