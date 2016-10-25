<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="project",
 *     indexes={
 *         @ORM\Index(name="user_id", columns={"user_id"}),
 *         @ORM\Index(name="test_consumer_key", columns={"test_consumer_key"}),
 *         @ORM\Index(name="live_consumer_key", columns={"live_consumer_key"}),
 *     }
 * )
 */
class Project implements EntityInterface
{
    const PROJECT_STATUS_APPLICATION_SENT = 'application_sent';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="user_id", type="string", length=255, nullable=true)
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="test_consumer_key", type="string", length=255, nullable=true)
     * @var string
     */
    protected $testConsumerKey;

    /**
     * @ORM\Column(name="live_consumer_key", type="string", length=255, nullable=true)
     * @var string
     */
    protected $liveConsumerKey;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $updated;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Project
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestConsumerKey()
    {
        return $this->testConsumerKey;
    }

    /**
     * @param string $testConsumerKey
     * @return Project
     */
    public function setTestConsumerKey($testConsumerKey)
    {
        $this->testConsumerKey = $testConsumerKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Project
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
