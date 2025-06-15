<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'conversation', cascade: ['persist', 'remove'])]
    private ?Order $order = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist'], orphanRemoval: true)]
    private Collection $messages;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    private ?User $client = null;

    #[ORM\ManyToOne]
    private ?User $artist = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    private ?Artwork $artwork = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isDeletedByClient = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isDeletedByArtist = false;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function isDeletedByBoth(): bool
    {
        return $this->isDeletedByClient && $this->isDeletedByArtist;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

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
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

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

    public function isParticipant(User $user): bool
    {
        return $this->client === $user || $this->artist === $user;
    }

    public function getOtherParticipant(User $currentUser): ?User
    {
        if ($this->client === $currentUser) {
            return $this->artist;
        }
        
        if ($this->artist === $currentUser) {
            return $this->client;
        }
        
        return null;
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

    public function isDeletedByClient(): ?bool
    {
        return $this->isDeletedByClient;
    }

    public function setIsDeletedByClient(bool $isDeletedByClient): static
    {
        $this->isDeletedByClient = $isDeletedByClient;

        return $this;
    }

    public function isDeletedByArtist(): ?bool
    {
        return $this->isDeletedByArtist;
    }

    public function setIsDeletedByArtist(bool $isDeletedByArtist): static
    {
        $this->isDeletedByArtist = $isDeletedByArtist;

        return $this;
    }
}
