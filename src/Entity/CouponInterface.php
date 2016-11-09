<?php

namespace CultuurNet\ProjectAanvraag\Entity;

interface CouponInterface extends EntityInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return CouponInterface
     */
    public function setCode($code);

    /**
     * @return boolean
     */
    public function isUsed();

    /**
     * @param boolean $used
     * @return CouponInterface
     */
    public function setUsed($used);
}
