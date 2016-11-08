<?php

namespace CultuurNet\ProjectAanvraag\Entity;

interface UserInterface extends EntityInterface, \JsonSerializable
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return User
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getInsightlyContactId();

    /**
     * @param string $insightlyContactId
     * @return User
     */
    public function setInsightylContactId($insightlyContactId);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName);

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getNick();

    /**
     * @param string $nick
     * @return User
     */
    public function setNick($nick);
}
