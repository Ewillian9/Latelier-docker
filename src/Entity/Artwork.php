<?php

namespace App\Entity;

use App\Repository\ArtworkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtworkRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Artwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $keywords = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'artwork', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'artwork')]
    private Collection $orders;

    /**
     * @var Collection<int, ArtworkImage>
     */
    #[ORM\OneToMany(targetEntity: ArtworkImage::class, mappedBy: 'artwork', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'artworks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $artist = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setArtwork($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArtwork() === $this) {
                $comment->setArtwork(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setArtwork($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getArtwork() === $this) {
                $order->setArtwork(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ArtworkImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ArtworkImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setArtwork($this);
        }

        return $this;
    }

    public function removeImage(ArtworkImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getArtwork() === $this) {
                $image->setArtwork(null);
            }
        }

        return $this;
    }

    public function getArtist(): ?User
    {
        return $this->artist;
    }

    public function setArtist(?User $artist): static
    {
        $this->artist = $artist;

        return $this;
    }
}
