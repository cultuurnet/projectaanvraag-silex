<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use JMS\Serializer\Annotation\Type;

class RequestedActivation extends ProjectEvent
{
    /**
     * @Type("string")
     * @var string
     */
    private $name;

    /**
     * @Type("string")
     * @var string
     */
    private $paymentEmail;

    /**
     * @Type("CultuurNet\ProjectAanvraag\Address")
     * @var Address
     */
    private $address;

    /**
     * @Type("string")
     * @var string
     */
    private $vatNumber;

    /**
     * ProjectActivated constructor.
     * @param ProjectInterface $project
     *   Project that was requested to activated
     * @param $email
     * @param $name
     * @param $address
     * @param $vatNumber
     */
    public function __construct(ProjectInterface $project, $email, $name, $address, $vatNumber)
    {
        parent::__construct($project);

        $this->paymentEmail = $email;
        $this->name = $name;
        $this->address = $address;
        $this->vatNumber = $vatNumber;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RequestedActivation
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentEmail()
    {
        return $this->paymentEmail;
    }

    /**
     * @param string $email
     * @return RequestedActivation
     */
    public function setPaymentEmail($email)
    {
        $this->email = paymentEmail;
        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     * @return RequestedActivation
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * @param string $vatNumber
     * @return RequestedActivation
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }
}
