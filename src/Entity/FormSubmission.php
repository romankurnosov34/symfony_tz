<?php

namespace App\Entity;

use App\Repository\FormSubmissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormSubmissionRepository::class)]
#[ORM\Table(name: 'form_submissions')]
class FormSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;
    
    #[ORM\Column(length: 255)]
    private ?string $email = null;
    
    #[ORM\ManyToOne(targetEntity: Color::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Color $color = null;
    
    #[ORM\ManyToOne(targetEntity: Shape::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shape $shape = null;
    
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $images = [];
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getShape(): ?Shape
    {
        return $this->shape;
    }

    public function setShape(?Shape $shape): self
    {
        $this->shape = $shape;
        return $this;
    }

    public function getImages(): array
    {
        return $this->images ?: [];
    }

    public function setImages(?array $images): self
    {
        $this->images = $images ?: [];
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getColorName(): string
    {
        return $this->color ? $this->color->getName() : '';
    }

    public function getShapeName(): string
    {
        return $this->shape ? $this->shape->getName() : '';
    }

    public function getImagesCount(): int
    {
        return count($this->getImages());
    }
}
