<?php

namespace App\Entity;

use App\Enum\InfoPageCategory;
use App\Repository\InfoPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: InfoPageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class InfoPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(enumType: InfoPageCategory::class)]
    private InfoPageCategory $category = InfoPageCategory::PAGE;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\PrePersist]
    public function initOnCreate(): void
    {
        $this->createdAt = new \DateTime();
        $this->updateSlug();
    }

    #[ORM\PreUpdate]
    public function initOnUpdate(): void
    {
        $this->updatedAt = new \DateTime();
        $this->updateSlug();
    }

    private function updateSlug(): void
    {
        if ($this->title) {
            $this->slug = (new AsciiSlugger())->slug(strtolower($this->title));
        }
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): ?string { return $this->slug; }

    public function getContent(): ?string { return $this->content; }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCategory(): InfoPageCategory { return $this->category; }

    public function setCategory(InfoPageCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function isPublished(): bool { return $this->isPublished; }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }

    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }
}
