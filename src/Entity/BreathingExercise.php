<?php

namespace App\Entity;

use App\Repository\BreathingExerciseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BreathingExerciseRepository::class)]
class BreathingExercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private int $inspirationDuration = 0;

    #[ORM\Column]
    private int $apneaDuration = 0;

    #[ORM\Column]
    private int $expirationDuration = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getInspirationDuration(): int { return $this->inspirationDuration; }
    public function setInspirationDuration(int $inspirationDuration): static { $this->inspirationDuration = $inspirationDuration; return $this; }

    public function getApneaDuration(): int { return $this->apneaDuration; }
    public function setApneaDuration(int $apneaDuration): static { $this->apneaDuration = $apneaDuration; return $this; }

    public function getExpirationDuration(): int { return $this->expirationDuration; }
    public function setExpirationDuration(int $expirationDuration): static { $this->expirationDuration = $expirationDuration; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getTotalDuration(): int
    {
        return $this->inspirationDuration + $this->apneaDuration + $this->expirationDuration;
    }

    public function __toString(): string { return $this->name ?? ''; }
}
