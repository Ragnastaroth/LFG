<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Game::class, inversedBy: 'users')]
    private Collection $games;

    #[ORM\OneToMany(mappedBy: 'likee', targetEntity: Like::class)]
    private Collection $likes;

    #[ORM\Column]
    private ?bool $mentor = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Profile::class)]
    private Collection $profiles;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Friendship::class)]
    private Collection $sentFriendRequests;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: Friendship::class, cascade: ['persist', 'remove'])]
    private Collection $receivedFriendRequests;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'writer', targetEntity: Comment::class)]
    private Collection $writer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    
    public function __construct() 
    {   
        $this->roles[] = 'ROLE_USER';
        $this->games = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $this->sentFriendRequests = new ArrayCollection();
        $this->receivedFriendRequests = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->writer = new ArrayCollection();     
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        $this->games->removeElement($game);

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setLiker($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getLiker() === $this) {
                $like->setLiker(null);
            }
        }

        return $this;
    }

    public function isLikedByCurrentUser(User $currentUser): bool
    {
    $likes = $this->likes;

    foreach ($likes as $like) {
        if ($like->getLiker() === $currentUser) {
            return true;
        }
    }

    return false;
    }

    public function isMentor(): ?bool
    {
        return $this->mentor;
    }

    public function setMentor(bool $mentor): self
    {
        $this->mentor = $mentor;

        return $this;
    }

    /**
     * @return Collection<int, Profile>
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles->add($profile);
            $profile->setUser($this);
        }

        return $this;
    }

    public function removeProfile(Profile $profile): self
    {
        if ($this->profiles->removeElement($profile)) {
            // set the owning side to null (unless already changed)
            if ($profile->getUser() === $this) {
                $profile->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Friendship>
     */
    public function getReceivedFriendRequests(): Collection
    {
        return $this->receivedFriendRequests;
    }

    public function removeReceivedFriendRequest(Friendship $friendship): void
    {
        $this->receivedFriendRequests->removeElement($friendship);              //????
    }

    public function sendFriendRequest(User $recipient): void
    {
        $friendship = new Friendship();
        $friendship->setSender($this);
        $friendship->setRecipient($recipient);

        $this->sentFriendRequests->add($friendship);
        $recipient->getReceivedFriendRequests()->add($friendship);
    }

    public function acceptFriendRequest(Friendship $friendship): void
    {
        if ($friendship->getRecipient() === $this) {
            $friendship->setAccepted(true);
        }
    }

    public function removeFriendRequest(Friendship $friendship): void
    {
        $this->sentFriendRequests->removeElement($friendship);
        $this->receivedFriendRequests->removeElement($friendship);
    }

    public function getFriends(): Collection
    {
        $friends = new ArrayCollection();

        foreach ($this->sentFriendRequests as $friendship) {
            if ($friendship->isAccepted()) {
                $friends->add($friendship->getRecipient());
            }
        }

        foreach ($this->receivedFriendRequests as $friendship) {
            if ($friendship->isAccepted()) {
                $friends->add($friendship->getSender());
            }
        }

        return $friends;
    }

    /**
     * Check if the user has a pending friend request from another user
     *
     * @param User $user The user to check for pending friend request
     * @return bool
     */
    public function hasPendingFriendRequestFrom(User $user): bool
    {
        foreach ($this->receivedFriendRequests as $friendship) {
            if ($friendship->getSender() === $user && !$friendship->isAccepted()) {
                return true;
            }
        }

        return false;
    }

    public function isFriendWith(User $user): bool
    {
        foreach ($this->sentFriendRequests as $friendship) {
            if ($friendship->isAccepted() && $friendship->getRecipient() === $user) {
                return true;
            }
        }

        foreach ($this->receivedFriendRequests as $friendship) {
            if ($friendship->isAccepted() && $friendship->getSender() === $user) {
                return true;
            }
        }

        return false;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getWriter(): Collection
    {
        return $this->writer;
    }

    public function addWriter(Comment $writer): self
    {
        if (!$this->writer->contains($writer)) {
            $this->writer->add($writer);
            $writer->setWriter($this);
        }

        return $this;
    }

    public function removeWriter(Comment $writer): self
    {
        if ($this->writer->removeElement($writer)) {
            // set the owning side to null (unless already changed)
            if ($writer->getWriter() === $this) {
                $writer->setWriter(null);
            }
        }

        return $this;
    }
}

