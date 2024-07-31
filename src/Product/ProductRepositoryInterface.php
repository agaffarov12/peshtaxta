<?php
declare(strict_types=1);

namespace Product;

interface ProductRepositoryInterface
{
    public function add(Product $product);
    public function delete(ProductId | string $id);
    public function findById(ProductId | string $id);
    public function findAll();
}
