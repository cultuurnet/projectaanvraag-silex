<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="coupon",
 *     indexes={
 *         @ORM\Index(name="code", columns={"code"}),
 *     }
 * )
 */
class Coupon
{

    /**
     * @ORM\Column(name="code", type="string", length=255)
     * @ORM\Id
     * @Type("string")
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(name="used", type="boolean")
     * @Type("boolean")
     * @var boolean
     */
    protected $used;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Coupon
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * @param boolean $used
     * @return Coupon
     */
    public function setUsed($used)
    {
        $this->used = $used;
        return $this;
    }
}
