<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="user",
 *     indexes={
 *         @ORM\Index(name="id", columns={"id"}),
 *         @ORM\Index(name="insightly_contact_id", columns={"insightly_contact_id"}),
 *     }
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     * @Type("string")
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(name="insightly_contact_id", type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $insightlyContactId;

    /**
     * @var string
     * @Type("string")
     */
    protected $firstName;

    /**
     * @var string
     * @Type("string")
     */
    protected $lastName;

    /**
     * @var string
     * @Type("string")
     */
    protected $email;

    /**
     * @var string
     * @Type("string")
     */
    protected $nick;

    /**
     * User constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getInsightlyContactId()
    {
        return $this->insightlyContactId;
    }

    public function setInsightylContactId($insightlyContactId)
    {
        $this->insightlyContactId = $insightlyContactId;
        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getNick()
    {
        return $this->nick;
    }

    public function setNick($nick)
    {
        $this->nick = $nick;
        return $this;
    }

    public function jsonSerialize()
    {
        $json = [];

        foreach ($this as $key => $value) {
            if (!empty($value)) {
                $json[$key] = $value;
            }
        }

        return $json;
    }
}
