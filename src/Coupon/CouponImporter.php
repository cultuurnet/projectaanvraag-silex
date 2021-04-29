<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

use CultuurNet\ProjectAanvraag\Entity\Coupon;
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

    public function import($filePath)
    {
        $file = new \SplFileObject($filePath);

        $coupons = [];
        // Construct all possible coupons.
        while (!$file->eof()) {
            $code = trim(preg_replace('/\s\s+/', ' ', $file->fgets()));
            if (!empty($code)) {
                $coupons[$code] = $code;
            }
        }

        // Remove all existing coupons.
        $repo = $this->entityManager->getRepository('ProjectAanvraag:Coupon');
        $existingCoupons = $repo->findBy(['code' => $coupons]);

        /** @var Coupon $coupon */
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

        return count($coupons);
    }
}
