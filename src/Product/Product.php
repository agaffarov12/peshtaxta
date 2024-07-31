<?php
declare(strict_types=1);

namespace Product;

use App\Doctrine\ProductIdType;
use App\Dto\BookingDto;
use Common\Entity\File;
use Common\Entity\Tag;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ChangeTrackingPolicy;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;
use function PHPUnit\Framework\assertNotEmpty;

#[Entity]
#[Table(name: "products")]
#[ChangeTrackingPolicy("DEFERRED_EXPLICIT")]
#[Index(fields: ["name"], name: "name_idx")]
#[Index(fields: ["type"], name: "product_type_idx")]
#[Index(fields: ["deleted"], name: "deleted_product_idx")]
#[Index(fields: ['city'], name: "city_idx")]
#[Index(fields: ['region'], name: "region_idx")]
class Product implements JsonSerializable
{
    #[Id]
    #[Column(type: ProductIdType::NAME)]
    private ProductId $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::BOOLEAN)]
    private bool $deleted;

    #[Column(type: Types::STRING)]
    private string $region;

    #[Column(type: Types::STRING)]
    private string $city;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[Column(type: Types::FLOAT, nullable: true)]
    private ?float $width;

    #[Column(type: Types::FLOAT, nullable: true)]
    private ?float $height;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $comment;

    #[Column(type: Types::JSON)]
    private array $misc;

    #[Column(type: Types::STRING, nullable: false, enumType: ProductType::class)]
    private ProductType $type;

    #[Embedded(class: Location::class)]
    private Location $location;

    #[ManyToOne(targetEntity: ProductCategory::class)]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    private ProductCategory $category;

    #[JoinTable(name: 'product_placements')]
    #[JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'placement_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: ProductPlacement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $placements;

    #[JoinTable(name: 'product_bookings')]
    #[JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'booking_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: Booking::class, cascade: ['persist', 'remove'], orphanRemoval: true ,indexBy: "id")]
    private Collection $bookings;

    #[JoinTable(name: 'product_files')]
    #[JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'file_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: File::class, cascade: ['persist', 'remove'])]
    private Collection $files;

    #[JoinTable(name: 'product_tags')]
    #[JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[ManyToMany(targetEntity: Tag::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tags;

    public function __construct(
        string          $name,
        string          $region,
        string          $city,
        ?float          $width,
        ?float          $height,
        ProductType     $type,
        ProductCategory $category,
        Location        $location,
        array           $files,
        array           $tags,
        array           $misc,
        string          $comment = null
        //BookingStrategy $bookingStrategy
    ) {
        $this->id              = ProductId::generate();
        $this->name            = $name;
        $this->region          = $region;
        $this->city            = $city;
        $this->createdAt       = new DateTimeImmutable();
        $this->width           = $width;
        $this->height          = $height;
        $this->deleted         = false;
        $this->type            = $type;
        $this->category        = $category;
        $this->location        = $location;
        $this->misc            = $misc;
        $this->comment         = $comment;
        $this->placements      = new ArrayCollection();
        $this->files           = new ArrayCollection($files);
        $this->tags            = new ArrayCollection($tags);
        $this->bookings        = new ArrayCollection();
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     */
    public function book(Booking $booking, BookingStrategy $strategy, string $bookingToExtend = null): void
    {
        if ($bookingToExtend !== null) {
            $this->extendBooking($booking, $bookingToExtend);
        }

        $booking = $strategy->book($this->bookings, $booking);

        $this->bookings->set((string) $booking->getId(), $booking);

        if ($booking->getPriority() === BookingPriority::LOW) {
            $this->cancelBookingsBetween(
                $booking->getStartDate(),
                $booking->getEndDate(),
                $booking->getId(),
                $booking->getPriority()
            );
        }
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     */
    public function editBooking(Booking $booking, BookingDto $dto, BookingStrategy $strategy): BookingId
    {
        $this->bookings->remove($dto->id);

        $booking = $strategy->book($this->bookings, $booking);

        $this->bookings->set((string) $booking->getId(), $booking);

        if ($booking->getPriority() === BookingPriority::LOW) {
            $this->cancelBookingsBetween(
                $booking->getStartDate(),
                $booking->getEndDate(),
                $booking->getId(),
                $booking->getPriority()
            );
        }

        return $booking->getId();
    }

    public function removeBooking(BookingId $bookingId): void
    {
        //$booking = $this->bookings[(string) $bookingId] ?? null;

        unset($this->bookings[(string) $bookingId]);
    }

    /**
     * @throws BookingIntervalTooShortException
     */
    public function extendBooking(Booking &$booking, string $bookingToExtend): void
    {
        $extendedBooking = $this->getBookingById($bookingToExtend);

        if ($booking->getEndDate() > $extendedBooking->getEndDate() === false) {
            throw new BookingIntervalTooShortException();
        }

        $booking->setPriority(BookingPriority::MEDIUM);
        $extendedBooking->setStatus(BookingStatus::EXTENDED);
    }

    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlacements(): array
    {
        return $this->placements->toArray();
    }

    public function getType(): ProductType
    {
        return $this->type;
    }

    public function getPlacementById(string $id): ?ProductPlacement
    {
        /** @var ProductPlacement $p */
        foreach($this->placements as $p) {
            if ((string) $p->getId() === $id) {
                return $p;
            }
        }

        return null;
    }

    public function cancelBookingsBetween(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        BookingId $bookingId,
        BookingPriority $priority

    ): void
    {
        $offset = 0;
        $limit = 100;

        $criteria = Criteria::create()
            ->where(Criteria::expr()->lt("startDate", $endDate))
            ->andWhere(Criteria::expr()->gt("endDate", $startDate))
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $collection = $this->bookings->matching($criteria);

        while(!$collection->isEmpty()) {
            /** @var Booking $booking */
            foreach($collection as $booking) {
                if ($booking->getId() === $bookingId) {
                    continue;
                }

                if ($priority === BookingPriority::MEDIUM && $booking->getStatus() !== BookingStatus::EXTENDED) {
                    $booking->cancel();
                }

                if ($priority === BookingPriority::LOW) {
                    $booking->cancel();
                }
            }

            $collection = $this->bookings->matching($criteria->setFirstResult(++$offset * 100));
        }
    }

    public function getBookingById(string $id): Booking
    {
        return $this->bookings->get($id);
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function addPlacement(ProductPlacement $placement): void
    {
        $this->placements->add($placement);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setType(ProductType $type): void
    {
        $this->type = $type;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function setCategory(ProductCategory $category): void
    {
        $this->category = $category;
    }

    public function setPlacements(array $placements): void
    {
        $this->placements = new ArrayCollection($placements);
    }

    public function setTags(array $tags): void
    {
        $this->tags = new ArrayCollection($tags);
    }

    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    public function setMisc(array $misc): void
    {
        $this->misc = $misc;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function addFile(File $f): void
    {
        $this->files->add($f);
    }

    public function removeFile(int $key): void
    {
        $this->files->remove($key);
    }

    public function setPlacementsCollection(array $placements): void
    {
        $collection = new ArrayCollection();

        /** @var ProductPlacement $p */
        foreach ($placements as $p) {
            $collection->set((string) $p->getId(), $p);
        }

        $this->placements = $collection;
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'id'         => (string) $this->id,
            'name'       => $this->name,
            'region'     => $this->region,
            'city'       => $this->city,
            'width'      => $this->width,
            'height'     => $this->height,
            'type'       => $this->type->value,
            'comment'    => $this->comment,
            'location'   => $this->location->jsonSerialize(),
            'category'   => $this->category->jsonSerialize(),
            'placements' => array_map(fn(ProductPlacement $p) => $p->jsonSerialize(), $this->placements->toArray()),
            //'bookings'   => array_map(fn(Booking $b) => $b->jsonSerialize(), $this->bookings->toArray()),
            'files'      => $this->files->toArray(),
            'tags'       => $this->tags->toArray(),
        ], $this->misc) ;
    }
}
