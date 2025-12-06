<?php

namespace App\Entity;

use App\Repository\BloodRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BloodRequestRepository::class)]
class BloodRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $bloodType = null;

    #[ORM\Column(length: 150)]
    private ?string $hospitalName = null;

    #[ORM\Column(length: 80)]
    private ?string $city = null;

    #[ORM\Column]
    private ?int $unitsNeeded = null;

    #[ORM\Column(length: 10)]
    private ?string $urgency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;


    #[ORM\Column(length: 30)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 15)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'bloodRequests')]
    private ?User $createdBy = null;

    /**
     * @var Collection<int, DonationOffer>
     */
    #[ORM\OneToMany(targetEntity: DonationOffer::class, mappedBy: 'request', orphanRemoval: true)]
    private Collection $donationOffers;

    public function __construct()
    {
        $this->donationOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    public function setBloodType(string $bloodType): static
    {
        $this->bloodType = $bloodType;

        return $this;
    }

    public function getHospitalName(): ?string
    {
        return $this->hospitalName;
    }

    public function setHospitalName(string $hospitalName): static
    {
        $this->hospitalName = $hospitalName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getUnitsNeeded(): ?int
    {
        return $this->unitsNeeded;
    }

    public function setUnitsNeeded(int $unitsNeeded): static
    {
        $this->unitsNeeded = $unitsNeeded;

        return $this;
    }

    public function getUrgency(): ?string
    {
        return $this->urgency;
    }

    public function setUrgency(string $urgency): static
    {
        $this->urgency = $urgency;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;
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
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

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
            $donationOffer->setRequest($this);
        }

        return $this;
    }

    public function removeDonationOffer(DonationOffer $donationOffer): static
    {
        if ($this->donationOffers->removeElement($donationOffer)) {
            // set the owning side to null (unless already changed)
            if ($donationOffer->getRequest() === $this) {
                $donationOffer->setRequest(null);
            }
        }

        return $this;
    }
}
