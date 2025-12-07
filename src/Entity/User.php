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
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserProfile $userProfile = null;

    /**
     * @var Collection<int, BloodRequest>
     */
    #[ORM\OneToMany(targetEntity: BloodRequest::class, mappedBy: 'createdBy')]
    private Collection $bloodRequests;

    /**
     * @var Collection<int, DonationOffer>
     */
    #[ORM\OneToMany(targetEntity: DonationOffer::class, mappedBy: 'donor', orphanRemoval: true)]
    private Collection $donationOffers;

    public function __construct()
    {
        $this->bloodRequests = new ArrayCollection();
        $this->donationOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    public function setUserProfile(UserProfile $userProfile): static
    {
        // set the owning side of the relation if necessary
        if ($userProfile->getUser() !== $this) {
            $userProfile->setUser($this);
        }

        $this->userProfile = $userProfile;

        return $this;
    }

    /**
     * @return Collection<int, BloodRequest>
     */
    public function getBloodRequests(): Collection
    {
        return $this->bloodRequests;
    }

    public function addBloodRequest(BloodRequest $bloodRequest): static
    {
        if (!$this->bloodRequests->contains($bloodRequest)) {
            $this->bloodRequests->add($bloodRequest);
            $bloodRequest->setCreatedBy($this);
        }

        return $this;
    }

    public function removeBloodRequest(BloodRequest $bloodRequest): static
    {
        if ($this->bloodRequests->removeElement($bloodRequest)) {
            // set the owning side to null (unless already changed)
            if ($bloodRequest->getCreatedBy() === $this) {
                $bloodRequest->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DonationOffer>
     */
    public function getDonationOffers(): Collection
    {
        return $this->donationOffers;
    }

    public function addDonationOffer(DonationOffer $donationOffer): static
    {
        if (!$this->donationOffers->contains($donationOffer)) {
            $this->donationOffers->add($donationOffer);
            $donationOffer->setDonor($this);
        }

        return $this;
    }

    public function removeDonationOffer(DonationOffer $donationOffer): static
    {
        if ($this->donationOffers->removeElement($donationOffer)) {
            // set the owning side to null (unless already changed)
            if ($donationOffer->getDonor() === $this) {
                $donationOffer->setDonor(null);
            }
        }

        return $this;
    }
}
