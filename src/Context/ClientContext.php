<?php
namespace Flowy\DynamicBundle\Context;

use Flowy\CoreBundle\Entity\Client;
use Flowy\CoreBundle\Entity\User;
use Flowy\DynamicBundle\Service\DynamicDatabaseManager;

class ClientContext
{
    private ?User $user = null;

    private ?Client $client = null;

    public function __construct(private readonly DynamicDatabaseManager $dynamicDatabaseManager)
    {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->dynamicDatabaseManager->setClient($client);
        $this->client = $client;
        return $this;
    }

}