<?php

namespace App\Domain\Services;

use App\Domain\Common\Result;
use App\Domain\DTOs\Orders\OrderInputDTO;
use App\Domain\Entities\Order;
use App\Domain\Repositories\Orders\IOrderRepository;
use App\Domain\ValueObjects\Uuid;

use Config\Services;

class OrderService
{

  public function __construct(
		private readonly IOrderRepository $repository
	) {}

	public function listAll(): Result
	{
  	return $this->repository->listAll();
	}

	public function listById(string $id): Result
	{
		if (!($uuidRes = Uuid::create($id))->isSuccess)	return $uuidRes;
    return $this->repository->listById($uuidRes->data);
	}

	public function listByCustomer(string $customerId): Result
	{
		if (!($uuidRes = Uuid::create($customerId))->isSuccess) return $uuidRes;
		return $this->repository->listByCustomer($uuidRes->data);
	}

	public function register(OrderInputDTO $dto): Result
	{

		if (!($customerUuid = Uuid::create($dto->customerId))->isSuccess) return $customerUuid;

		$customerExists = Services::customerService()->listById($dto->customerId);
  	if (!$customerExists->isSuccess) return Result::failure("Operação inválida: O cliente informado não existe.", 202);

		$order = Order::create($dto, $customerUuid->data);
  	return $this->repository->register($order);

	}

	public function update(OrderInputDTO $dto, string $id): Result
	{

		if (!($uuidRes = Uuid::create($id))->isSuccess) return $uuidRes;

		$result = $this->repository->listById($uuidRes->data);
		if (!$result->isSuccess) return $result;

		$order = Order::restore($result->data);
		$order->updateFromDTO($dto);

		return $this->repository->update($order);

	}

	public function delete(string $id): Result
	{
		if (!($uuidRes = Uuid::create($id))->isSuccess) return $uuidRes;
		return $this->repository->delete($uuidRes->data);
	}

  private function groupOrdersByCustomer(array $orders): array
  {

    $grouped = [];

    foreach ($orders as $order) {

      $cId = (string)$order->getCustomerId();

      if (!isset($grouped[$cId])) {
        $grouped[$cId] = [
          'customer_id'		=> $cId,
          'customer_name'	=> $order->getCustomerName(),
          'customer_cpf'	=> (string)$order->getCustomerCpf(),
          'orders'				=> []
        ];
      }

      $grouped[$cId]['orders'][] = [
        'id'							=> (string)$order->getId(),
        'description'			=> $order->getDescription(),
        'total_value'			=> $order->getTotalValue(),
        'created_at'			=> $order->getCreatedAt()
      ];

    }

    return array_values($grouped);

  }

}