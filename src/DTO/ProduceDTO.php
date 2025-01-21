<?php

namespace App\DTO;

use App\Entity\Produce;
use App\Service\ProduceStorageService\DTO\DTOInterface;
use App\Service\ProduceStorageService\Enum\ProduceTypeEnum;
use App\Service\ProduceStorageService\Enum\UnitEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ProduceDTO implements DTOInterface
{
    #[Assert\Positive]
    private int $id;

    #[Assert\Length(min: 3, max: 255, minMessage: "The 'name' is too short")]
    private string $name;

    #[Assert\Positive]
    private int $quantity;

    #[Assert\Type(type: ProduceTypeEnum::class)]
    private ProduceTypeEnum $type;

    #[Assert\Type(type: UnitEnum::class)]
    private UnitEnum $unit = UnitEnum::GRAM;

    public function fillFromEntity(Produce $produce): static
    {
        $this->fill(
            $produce->getExternalId(),
            $produce->getName(),
            $produce->getQuantity(),
            $produce->getType(),
            UnitEnum::GRAM,
        );

        return $this;
    }

    /**
     * @param array $array
     *
     * @throws \ValueError|\TypeError
     *
     * @return $this
     */
    public function fillFromArray(array $array): static
    {
        foreach (['id', 'name', 'quantity', 'type', 'unit'] as $property) {
            if ( ! array_key_exists($property, $array) ) {
                throw new \InvalidArgumentException("'$property' key is required");
            }
        }

        $type = ProduceTypeEnum::tryFrom($array['type']);

        if ( ! $type ) {
            throw new \InvalidArgumentException("wrong 'type' option, available values are: " . implode(', ', ProduceTypeEnum::values()));
        }

        $unit = UnitEnum::tryFrom($array['unit']);

        if ( ! $unit ) {
            throw new \InvalidArgumentException("wrong 'unit' option, available values are: " . implode(', ', UnitEnum::values()));
        }

        $this->fill(
            $array['id'] ?? null,
            $array['name'] ?? null,
            $array['quantity'] * $unit->getCoefficient(),
            $type,
            UnitEnum::GRAM,
        );

        return $this;
    }

    public function fill(int $id, string $name, int $quantity, ProduceTypeEnum $type, UnitEnum $unit): static
    {
        $this
            ->setId         ($id        )
            ->setName       ($name      )
            ->setQuantity   ($quantity  )
            ->setType       ($type      )
            ->setUnit       ($unit      )
        ;

        return $this;
    }


    public function toArray(): array
    {
        return $this->serialize();
    }

    public function serialize(): array
    {
        return [
            'id'        => $this->getId(),
            'name'      => $this->getName(),
            'type'      => $this->getType()->value,
            'quantity'  => $this->getQuantity(),
            'unit'      => $this->getUnit()->value,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $externalId): static
    {
        $this->id = $externalId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getType(): ProduceTypeEnum
    {
        return $this->type;
    }

    public function setType(ProduceTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUnit(): UnitEnum
    {
        return $this->unit;
    }

    public function setUnit(UnitEnum $unit): static
    {
        if ($this->unit != $unit) {
            $this->quantity /= $unit->getCoefficient();
        }

        $this->unit = $unit;

        return $this;
    }
}