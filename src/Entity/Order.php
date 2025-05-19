<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $orderinguser = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Artwork $artwork = null;

    #[ORM\OneToOne(mappedBy: 'orderlink', cascade: ['persist', 'remove'])]
    private ?Conversation $conversation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getOrderinguser(): ?User
    {
        return $this->orderinguser;
    }

    public function setOrderinguser(?User $orderinguser): static
    {
        $this->orderinguser = $orderinguser;

        return $this;
    }

    public function getArtwork(): ?Artwork
    {
        return $this->artwork;
    }

    public function setArtwork(?Artwork $artwork): static
    {
        $this->artwork = $artwork;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        // unset the owning side of the relation if necessary
        if ($conversation === null && $this->conversation !== null) {
            $this->conversation->setOrderlink(null);
        }

        // set the owning side of the relation if necessary
        if ($conversation !== null && $conversation->getOrderlink() !== $this) {
            $conversation->setOrderlink($this);
        }

        $this->conversation = $conversation;

        return $this;
    }
}
