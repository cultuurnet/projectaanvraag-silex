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

    /** @var  EntityRepository|MockObject */
    protected $couponRepository;

    /**
     * Setup the service with mock objects.
     */
    protected function setUp(): void
    {
        $this->couponRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

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

    /**
     * Test if validator throws invalid coupon exception.
     * @expectedException \CultuurNet\ProjectAanvraag\Coupon\Exception\InvalidCouponException
     */
    public function testInValidCoupon()
    {
        $this->couponRepository->expects($this->once())
            ->method('find')
            ->with('coupon')
            ->willReturn(null);

        try {
            $this->validator->validateCoupon('coupon');
        } catch (InvalidCouponException $e) {
            $this->assertEquals($e->getValidationCode(), InvalidCouponException::ERROR_CODE);
            throw $e;
        }
    }

    /**
     * Test if validator throws invalid coupon exception.
     * @expectedException \CultuurNet\ProjectAanvraag\Coupon\Exception\CouponInUseException
     */
    public function testCouponInUse()
    {

        $coupon = new Coupon();
        $coupon->setUsed(true);

        $this->couponRepository->expects($this->once())
            ->method('find')
            ->with('coupon')
            ->willReturn($coupon);

        try {
            $this->validator->validateCoupon('coupon');
        } catch (CouponInUseException $e) {
            $this->assertEquals($e->getValidationCode(), CouponInUseException::ERROR_CODE);
            throw $e;
        }
    }
}
