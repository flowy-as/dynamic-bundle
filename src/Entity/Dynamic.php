<?php
declare(strict_types=1);

namespace Flowy\DynamicBundle\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity]
#[Index(columns: ['code'], unique: false)]
class Dynamic
{
    #[Column(type: "primary")]
    private int|null $id = null;

    #[Column(type: "string", nullable: false)]
    public string|null $code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

}