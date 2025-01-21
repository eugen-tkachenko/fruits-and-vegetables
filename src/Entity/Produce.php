<?php

namespace App\Entity;

use App\DTO\ProduceDTO;
use App\Repository\ProduceRepository;
use App\Service\ProduceStorageService\Enum\ProduceTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduceRepository::class)]
class Produce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    # id should not be used in API calls,
    # externalId is proposed to be used instead
    # like a slug or a simplified uuid
    #[ORM\Column]
    #[Assert\Positive]
    private int $externalId;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 3, max: 255)]
    private string $name;


    # Could easily be an "is_fruit" boolean field,
    # but normally it escalates quickly: extensibility previewed

    # Depending on the context, it may also be
    # a ManyToOne relation
    # or even an Inheritance Mapping case
    #[ORM\Column(type: 'string', enumType: ProduceTypeEnum::class)]
    #[Assert\Type(type: ProduceTypeEnum::class)]
    private ProduceTypeEnum $type;

    # Quantity in grams
    # The Unit column therefore rudimentary since everything is always in grams
    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(int $externalId): static
    {
        $this->externalId = $externalId;
        
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    
    public function getType(): ProduceTypeEnum
    {
        return $this->type;
    }

    public function setType(ProduceTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }


    /**
     * @param string $string
     *
     * @return static
     *
     * @throws \ValueError|\TypeError if ProduceTypeEnum case is not found
     */
    public function setTypeFromString(string $string): static
    {
        $this->setType(ProduceTypeEnum::from($string));

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public static function createFromDTO(ProduceDTO $produceDTO): static
    {
        return (new static())
            ->setExternalId($produceDTO->getId())
            ->setName($produceDTO->getName())
            ->setType($produceDTO->getType())
            ->setQuantity($produceDTO->getQuantity())
        ;
    }
}
