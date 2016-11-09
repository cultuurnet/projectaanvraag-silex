<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

use CultuurNet\ProjectAanvraag\Coupon\Exception\CouponInUseException;
use CultuurNet\ProjectAanvraag\Coupon\Exception\InvalidCouponException;
use CultuurNet\ProjectAanvraag\Entity\CouponInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Validates if coupons are correct.
 */
class CouponValidator implements CouponValidatorInterface
{

    /**
     * @var EntityRepository
     */
    protected $couponRepository;

    /**
     * CouponValidator constructor.
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->couponRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validateCoupon($coupon)
    {
        /** @var CouponInterface $couponEntity */
        $couponEntity = $this->couponRepository->find($coupon);
        if (empty($couponEntity)) {
            throw new InvalidCouponException();
        }

        if ($couponEntity->isUsed()) {
            throw new CouponInUseException();
        }
    }
}
