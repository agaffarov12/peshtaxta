<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use App\Dto\ProductDto;
use App\Dto\ProductPlacementDto;
use App\Utils\DateTimeFormatConverter;
use App\Utils\FileUtil;
use Client\ClientId;
use Common\Entity\File;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityNotFoundException;
use Laminas\Diactoros\UploadedFile;
use Money\Currency;
use Money\Money;
use Product\Booking;
use Product\BookingId;
use Product\BookingPriority;
use Product\BookingStatus;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;
use Product\ImageAdBookingStrategy;
use Product\Location;
use Product\PlacementStatus;
use Product\Product;
use Product\ProductId;
use Product\ProductPlacement;
use Product\ProductPlacementId;
use Product\ProductType;
use Product\VideoAdBookingStrategy;

class ProductsService
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly ProductCategoryService $productCategoryService,
        private readonly TagsService $tagsService,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function create(ProductDto $dto): void
    {
        $files = array_map(
            fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
            $dto->files
        );

        $tags = empty($dto->tags) ? [] : $this->tagsService->addTags($dto->tags);

        $product = new Product(
            $dto->name,
            $dto->region,
            $dto->city,
            $dto->width ? (float) $dto->width : null,
            $dto->height ? (float) $dto->height : null,
            ProductType::from($dto->type),
            $this->productCategoryService->get($dto->category),
            new Location($dto->location->latitude, $dto->location->longitude),
            $files,
            $tags,
            [
                'viewingDistance'        => $dto->viewingDistance ?? null,
                'trafficVolume'          => $dto->trafficVolume ?? null,
                'transportPosition'      => $dto->transportPosition ?? null,
                'distanceToTrafficLight' => $dto->distanceToTrafficLight ?? null,
            ],
            $dto->comment ?: null
            //new $mappings[$dto->type]
        );

        $placements = array_map(
            fn(ProductPlacementDto $placementDto) => new ProductPlacement(
                null,
                $placementDto->name,
                new Money($placementDto->price->amount, new Currency($placementDto->price->currency)),
                $product->getId(),
                array_map(
                    fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
                    $placementDto->images ?? []
                )
            ),
            $dto->placements
        );

        $product->setPlacementsCollection($placements);

        $this->repository->add($product);
    }

    /**
     * @throws EntityNotFoundException
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     */
    public function addBooking(string $productId, BookingDto $dto, string $bookingToExtend = null): BookingId
    {
        $mappings = [
            ProductType::IMAGE->value => ImageAdBookingStrategy::class,
            ProductType::VIDEO->value => VideoAdBookingStrategy::class,
            ProductType::AUDIO->value => VideoAdBookingStrategy::class,
        ];

        $product = $this->repository->findById($productId);

        $booking = new Booking(
            ClientId::fromString($dto->client),
            DateTimeFormatConverter::toDateTime($dto->startDate),
            DateTimeFormatConverter::toDateTime($dto->endDate),
            $product->getPlacementById($dto->placement),
            BookingPriority::from($dto->priority)
        );

        $product->book(
            $booking,
            new $mappings[$product->getType()->value],
            $bookingToExtend
        );

        $this->repository->add($product);

        return $booking->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function removeBooking(ProductId $productId, Booking $booking): void
    {
        $product = $this->repository->findById($productId);

        $product->removeBooking($booking->getId());

        //$this->repository->add($product);
    }

    /**
     * @throws EntityNotFoundException
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     */
    public function editBooking(ProductId $productId, BookingDto $dto): BookingId
    {
        $mappings = [
            ProductType::IMAGE->value => ImageAdBookingStrategy::class,
            ProductType::VIDEO->value => VideoAdBookingStrategy::class,
            ProductType::AUDIO->value => VideoAdBookingStrategy::class,
        ];

        $product = $this->repository->findById($productId);

        $booking = new Booking(
            ClientId::fromString($dto->client),
            DateTimeFormatConverter::toDateTime($dto->startDate),
            DateTimeFormatConverter::toDateTime($dto->endDate),
            $product->getPlacementById($dto->placement),
            BookingPriority::from($dto->priority)
        );

        $id = $product->editBooking($booking, $dto, new $mappings[$product->getType()->value]);

        $this->repository->add($product);

        return $id;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function LowerPriority(ProductId $productId, string $bookingId): void
    {
        $product = $this->repository->findById($productId);
        $booking = $product->getBookingById($bookingId);

        $booking->setPriority(BookingPriority::MEDIUM);

        $this->repository->add($product);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function changePlacementStatus(string $productId, string $placementId, PlacementStatus $status): void
    {
        $product = $this->repository->findById($productId);
        $placement = $product->getPlacementById($placementId);

        $placement->setStatus($status);

        $this->repository->add($product);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $id, ProductDto $dto): void
    {
        $product = $this->repository->findById($id);

        $uploadedFiles = array_map(
            fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
            $dto->files
        );

        $newFiles = $this->getNewFiles($product->getFiles()->toArray(), $uploadedFiles, $product);

        foreach($newFiles as $file) {
            $product->addFile($file);
        }

        if ($dto->name) {
            $product->setName($dto->name);
        }

        if ($dto->type) {
            $product->setType(ProductType::from($dto->type));
        }

        if ($dto->location) {
            $product->setLocation(new Location($dto->location->latitude, $dto->location->longitude));
        }

        if ($dto->category) {
            $product->setCategory($this->productCategoryService->get($dto->category));
        }

        if ($dto->comment) {
            $product->setComment($dto->comment);
        }

        if ($dto->tags) {
            $tags = $this->tagsService->addTags($dto->tags);

            $product->setTags($tags);
        }

        if ($dto->width) {
            $product->setWidth((float) $dto->width);
        }

        if ($dto->height) {
            $product->setHeight((float) $dto->height);
        }

        if ($dto->placements) {
           $this->editPlacements($dto->placements, $product);
        }

        if ($dto->region) {
            $product->setRegion($dto->region);
        }

        if ($dto->city) {
            $product->setCity($dto->city);
        }

        $product->setMisc([
            'viewingDistance'        => $dto->viewingDistance ?? null,
            'trafficVolume'          => $dto->trafficVolume ?? null,
            'transportPosition'      => $dto->transportPosition ?? null,
            'distanceToTrafficLight' => $dto->distanceToTrafficLight ?? null,
        ]);

        $this->repository->add($product);
    }

    private function editPlacements(array $placements, Product &$product): void
    {
        $productPlacements = [];

        /** @var ProductPlacementDto $p */
        foreach($placements as $p) {
            if (empty(trim($p->id)) === false && $p->id !== null) {
                $placement = $product->getPlacementById($p->id);

                $placement->setName($p->name);
                $placement->setPrice(new Money($p->price->amount, new Currency($p->price->currency)));

                $uploadedFiles = array_map(
                    fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
                    $p->images ?? []
                );

                $newFiles = $this->getNewPlacementImages($placement->getImages()->toArray(), $uploadedFiles, $placement);

                foreach($newFiles as $file) {
                    $placement->addImage($file);
                }

                $productPlacements[] = $placement;

            } else {
                $productPlacements[] = new ProductPlacement(
                    null,
                    $p->name,
                    new Money($p->price->amount, new Currency($p->price->currency)),
                    $product->getId(),
                    array_map(
                        fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
                        $p->images ?? []
                    )
                );
            }
        }

        $product->setPlacements($productPlacements);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function cancelBookingsBetween(
        ProductId $productId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        BookingId $bookingId,
        BookingPriority $priority,
    ): void {
        $product = $this->repository->findById($productId);

        $product->cancelBookingsBetween($startDate, $endDate, $bookingId, $priority);

        $this->repository->add($product);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $product = $this->repository->findById($id);

        $product->delete();

        $this->repository->add($product);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function editEndDateOfBooking(string $productId, string $bookingId): int
    {
        $product = $this->repository->findById($productId);
        $booking = $product->getBookingById($bookingId);

        $now = new DateTimeImmutable();
        $started = $now >= $booking->getStartDate() && $now < $booking->getEndDate();

        if ($started) {
            $booking->setEndDate($now);

            $this->repository->add($product);
        }

        $booking->cancel();

        return $booking->calculateThePrice();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function cancelBooking(ProductId $productId, BookingId $bookingId): void
    {
        $product = $this->repository->findById($productId);
        $booking = $product->getBookingById((string) $bookingId);

        $booking->cancel();

        $this->repository->add($product);
    }

    private function getNewFiles(array $productFiles, array $uploadedFiles, Product &$product): array
    {
        $newFiles = [];
        $filesToDelete = [];

        foreach($uploadedFiles as $key => $uploadedFile) {
            foreach ($productFiles as $productFile) {
                if (FileUtil::filesIdentical($uploadedFile->getPath(), $productFile->getPath())) {
                    $filesToDelete[] = $uploadedFile->getPath();
                    continue 2;
                }
            }
            $newFiles[] = $uploadedFile;
        }

        /** @var File $value */
        foreach($product->getFiles() as $key => $value) {
            foreach ($uploadedFiles as $uploadedFile) {
                if (FileUtil::filesIdentical($uploadedFile->getPath(), $value->getPath())) {
                    continue 2;
                }
            }
            $product->removeFile($key);
        }

        foreach ($filesToDelete as $f) {
            FileUtil::deleteFile($f);
        }
        return $newFiles;
    }

    private function getNewPlacementImages(
        array $placementImages,
        array $uploadedFiles,
        ProductPlacement &$placement
    ): array
    {
        $newFiles = [];
        $filesToDelete = [];

        foreach($uploadedFiles as $key => $uploadedFile) {
            foreach ($placementImages as $placementImage) {
                if (FileUtil::filesIdentical($uploadedFile->getPath(), $placementImage->getPath())) {
                    $filesToDelete[] = $uploadedFile->getPath();
                    continue 2;
                }
            }
            $newFiles[] = $uploadedFile;
        }

        /** @var File $value */
        foreach($placement->getImages() as $key => $value) {
            foreach ($uploadedFiles as $uploadedFile) {
                if (FileUtil::filesIdentical($uploadedFile->getPath(), $value->getPath())) {
                    continue 2;
                }
            }
            $placement->removeImage($key);
        }

        foreach ($filesToDelete as $f) {
            FileUtil::deleteFile($f);
        }
        return $newFiles;
    }
}
