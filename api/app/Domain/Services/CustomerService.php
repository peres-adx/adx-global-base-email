<?php

namespace App\Domain\Services;

use App\Domain\Common\{BaseMapper, Result};
use App\Domain\DTOs\Customers\CustomerInputDTO;
use App\Domain\Entities\Customer;
use App\Domain\Repositories\Customers\ICustomerRepository;
use App\Domain\Repositories\Users\IUserRepository;
use App\Domain\ValueObjects\{Email, Cpf, Uuid};

use Config\Services;

class CustomerService
{

  public function __construct(
    private readonly ICustomerRepository	$repository,
		private readonly IUserRepository			$userRepository
  ) {}

	public function listAll(): Result
	{
  	return $this->repository->listAll();
	}

	public function listById(string $id): Result
	{
		if (!($uuidRes  = Uuid::create($id))->isSuccess) return $uuidRes;
    return $this->repository->listById($uuidRes->data);
	}

	public function listByUser(string $userId): Result
	{
		if (!($uuidRes = Uuid::create($userId))->isSuccess) return $uuidRes;
    return $this->repository->listByUser($uuidRes->data);
	}

	public function listByCpf(string $cpf): Result
	{
		if (!($cpfRes = Cpf::create($cpf))->isSuccess) return $cpfRes;
    return $this->repository->listByCpf($cpfRes->data);
	}

	public function listByEmail(string $email): Result
	{
		if (!($emailRes = Email::create($email))->isSuccess) return $emailRes;
    return $this->repository->listByEmail($emailRes->data);
	}

	public function register(CustomerInputDTO $dto): Result
	{

		if (!($emailRes = Email::create($dto->email))->isSuccess)				return $emailRes;
		if (!($cpfRes   = Cpf::create($dto->cpf))->isSuccess)						return $cpfRes;
		if (!($uuidRes  = Uuid::create($dto->userId))->isSuccess)				return $uuidRes;

		if (!$this->userRepository->existsById($uuidRes->data))					return Result::failure("O Usuário informado (userId) não existe.", 404);
		if ($this->repository->exists($emailRes->data, $cpfRes->data))	return Result::failure("E-mail ou CPF já cadastrado para outro cliente.", 202);

		$customer = Customer::create($dto, $emailRes->data, $cpfRes->data, $uuidRes->data);
		$result   = $this->repository->register($customer);

		if (!$result->isSuccess) return $result;

		$data				= $result->data;
		$data['id'] = strtoupper(str_replace('-', '', $data['id']));
		if (isset($data['user_id'])) $data['user_id'] = strtoupper(str_replace('-', '', $data['user_id']));

		return Result::success($data, $result->message, 201);

	}

	public function update(CustomerInputDTO $dto, string $id): Result
	{

		if (!($uuidRes  = Uuid::create($id))->isSuccess)						return $uuidRes;
		if (!($emailRes = Email::create($dto->email))->isSuccess)		return $emailRes;
		if (!($cpfRes   = Cpf::create($dto->cpf))->isSuccess)				return $cpfRes;

		$result = $this->repository->listById($uuidRes->data);
		if (!$result->isSuccess) return $result;

		if ($this->repository->existsForOther($emailRes->data, $cpfRes->data, $uuidRes->data)) return Result::failure("Os dados informados (E-mail ou CPF) já estão em uso por outro cliente.", 202);

		$customer = Customer::restore($result->data);
		$customer->updateFromDTO($dto, $emailRes->data, $cpfRes->data);

		return $this->repository->update($customer);

	}

  public function delete(string $id): Result
	{
		if (!($uuidRes = Uuid::create($id))->isSuccess)	return $uuidRes;
		return $this->repository->delete($uuidRes->data);
	}

}