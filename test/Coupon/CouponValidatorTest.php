<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

use CultuurNet\ProjectAanvraag\Coupon\Exception\CouponInUseException;
use CultuurNet\ProjectAanvraag\Coupon\Exception\InvalidCouponException;
use CultuurNet\ProjectAanvraag\Entity\Coupon;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the CouponValidator class.
 */
class CouponValidatorTest extends TestCase
{

    /** @var  CouponValidatorInterface */
    protected $validator;

    /** @var  EntityRepository & MockObject */
    protected $couponRepository;

    /**
     * Setup the service with mock objects.
     */
    public function setUp()
    {
        $this->couponRepository = $this->createMock(EntityRepository::class);

        $this->validator = new CouponValidator($this->couponRepository);
    }

    /**
     * Test if validator gives no exceptions when a valid coupon is given
     */
    public function testValidCoupon()
    {
        $coupon = new Coupon();
        $coupon->setUsed(false);

        $this->couponRepository->expects($this->once())
            ->method('find')
            ->with('coupon')
            ->willReturn($coupon);

        $this->validator->validateCoupon('coupon');
    }

    public function testInValidCouponException()
    {
        $this->couponRepository->expects($this->once())
            ->method('find')
            ->with('coupon')
            ->willReturn(null);

        $this->expectException(InvalidCouponException::class);

        $this->validator->validateCoupon('coupon');
    }

    public function testCouponInUseException()
    {
        $coupon = new Coupon();
        $coupon->setUsed(true);

        $this->couponRepository->expects($this->once())
            ->method('find')
            ->with('coupon')
            ->willReturn($coupon);

        $this->expectException(CouponInUseException::class);

        $this->validator->validateCoupon('coupon');
    }
}
