<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;

/**
 * Request activation command.
 */
class RequestActivation extends ProjectCommand
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $paymentEmail;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var string
     */
    private $vatNumber;

    /**
     * RequestActivation constructor.
     * @param ProjectInterface $project
     * @param $email
     * @param $name
     * @param $address
     * @param $vatNumber
     */
    public function __construct(ProjectInterface $project, $name, $address, $vatNumber = '', $paymentEmail = '')
    {
        parent::__construct($project);
        $this->paymentEmail = $paymentEmail;
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
     * @return RequestActivation
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
     * @return RequestActivation
     */
    public function setPaymentEmail($email)
    {
        $this->paymentEmail = $email;
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
     * @return RequestActivation
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
     * @return RequestActivation
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }
}
