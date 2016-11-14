<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

use CultuurNet\ProjectAanvraag\Coupon\Exception\CouponInUseException;
use CultuurNet\ProjectAanvraag\Coupon\Exception\InvalidCouponException;
use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\CouponInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Entity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Imports coupons
 */
class CouponImporter
{

    /**
     * @var EntityRepository
     */
    protected $couponRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * CouponValidator constructor.
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository, EntityManager $entityManager)
    {
        $this->couponRepository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function import($filePath)
    {
        $file = new \SplFileObject($filePath);

        $coupons = [];
        // Construct all possible coupons.
        while (!$file->eof()) {
            $code = trim(preg_replace('/\s\s+/', ' ', $file->fgets()));
            $coupons[$code] = $code;
        }

        // Remove all existing coupons.
        $repo = $this->entityManager->getRepository('ProjectAanvraag:Coupon');
        $existingCoupons = $repo->findBy(['code' => $coupons]);
        foreach ($existingCoupons as $coupon) {
            unset($coupons[$coupon->getCode()]);
        }

        // Insert the new coupons.
        foreach ($coupons as $code) {
            $coupon = new Coupon();
            $coupon->setCode($code);
            $coupon->setUsed(false);
            $this->entityManager->persist($coupon);
        }

        $this->entityManager->flush();
    }
}
