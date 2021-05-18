<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use JMS\Serializer\Annotation\Type;

class RequestedActivation extends AbstractProjectEvent
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
    private $email;

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

    public function __construct(
        ProjectInterface $project,
        string $email,
        string $name,
        Address $address,
        string $vatNumber
    ) {
        parent::__construct($project);

        $this->email = $email;
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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }
}
