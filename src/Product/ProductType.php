<?php
declare(strict_types=1);

namespace Product;

enum ProductType: string
{
    case IMAGE = "image";
    case VIDEO = "video";
    case AUDIO = "audio";
}
