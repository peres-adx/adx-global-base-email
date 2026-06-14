<?php

namespace App\Domain\DTOs\Orders;

use App\Domain\Entities\Order;

final readonly class OrderOutputDTO
{

  public function __construct(
    public string		$id,
    public string		$customerId,
    public string		$description,
    public float		$totalValue,
    public ?string	$createdAt	= null,
  ) {}

  public static function fromEntity(Order $order): self
  {
    return new self(
      id:           (string) $order->getId(),
      customerId:   (string) $order->getCustomerId(),
      description:  $order->getDescription(),
      totalValue:   (float)  $order->getTotalValue(),
      createdAt:    $order->getCreatedAt()
    );
  }

}