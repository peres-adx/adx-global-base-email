<?php

namespace App\Domain\Services;

use App\Domain\Common\Result;
use App\Domain\DTOs\Users\{PasswordInputDTO, UserInputDTO};
use App\Domain\Entities\User;
use App\Domain\Repositories\Users\{IUserRepository, IUserTokenRepository};
use App\Domain\ValueObjects\{Cpf, Email, Password, Uuid};

use Config\Services;

class UserService
{

	public function __construct(
		private readonly IUserRepository				$repository,
		private readonly IUserTokenRepository		$tokenRepository
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

	public function listByEmail(string $email): Result
	{
		if (!($emailRes = Email::create($email))->isSuccess)	return $emailRes;
    return $this->repository->listByEmail($emailRes->data);
	}

	public function register(UserInputDTO $dto): Result
	{

		if (!($emailRes = Email::create($dto->email))->isSuccess)																		return $emailRes;
		if (!($cpfRes   = ($dto->cpf ? Cpf::create($dto->cpf) : Result::success(null)))->isSuccess)	return $cpfRes;
		if (!($passRes  = Password::create($dto->password))->isSuccess)															return $passRes;
		if ($this->repository->exists($emailRes->data, $cpfRes->data)) 															return Result::failure("E-mail ou CPF já cadastrado.", 202);

		$user			= User::create($dto, $emailRes->data, $passRes->data, $cpfRes->data);
		$result		= $this->repository->register($user);

		if (!$result->isSuccess) return $result;

		$data = $result->data;
		$data['id'] = strtoupper(str_replace('-', '', $data['id']));
		unset($data['password']);

		return Result::success($data, $result->message, 201);

	}

	public function update(UserInputDTO $dto, string $id): Result
	{

		if (!($uuidRes  = Uuid::create($id))->isSuccess)																							return $uuidRes;
		if (!($emailRes = Email::create($dto->email))->isSuccess)																			return $emailRes;
		if (!($cpfRes   = ($dto->cpf ? Cpf::create($dto->cpf) : Result::success(null)))->isSuccess) 	return $cpfRes;

		$user = $this->repository->listById($uuidRes->data);
		if (!$user) return Result::failure("Usuário não localizado.");

		if ($this->repository->exists($emailRes->data, $cpfRes->data, $uuidRes->data)) 								return Result::failure("E-mail ou CPF já cadastrado para outro usuário.", 202);

		$result = $this->repository->listById($uuidRes->data);
		if (!$result->isSuccess) return $result;

		$user = User::restore($result->data);
		$user->updateFromDTO($dto, $emailRes->data, $cpfRes->data);

		return $this->repository->update($user);

	}

	public function delete(string $id, string $loggedUserId): Result
	{

		if (!($uuidRes = Uuid::create($id))->isSuccess) return $uuidRes;

		$targetId		= strtoupper(str_replace('-', '', $id));
		$currentId	= strtoupper(str_replace('-', '', $loggedUserId));

		if ($targetId === $currentId)												return Result::failure("Não é permitido excluir o próprio usuário logado.", 202);
  	if (!$this->repository->existsById($uuidRes->data))	return Result::failure("Usuário não encontrado.", 202);

  	return $this->repository->delete($uuidRes->data);

	}

	public function createMasterUser(): Result
	{

		$masterEmail    = 'master@adx-global-base.com';
		$masterPassword	= '123456';

		$emailRes = Email::create($masterEmail);
		if (!$emailRes->isSuccess) return Result::failure("E-mail master inválido.", 202);

		$existingUser = $this->repository->listByEmail($emailRes->data);
		if ($existingUser->isSuccess) return Result::failure("Usuário Master já está configurado.", 202);

		$dto = new UserInputDTO(
			'Administrador Master',
			$masterEmail,
			$masterPassword,
			'00000000000',
			null, null, null, null, null, null, null, null
		);

		$cpfRes		= Cpf::create($dto->cpf);
		$passRes	= Password::create($dto->password);
		$user			= User::create($dto, $emailRes->data, $passRes->data, $cpfRes->data);

		$saveRes = $this->repository->register($user);
		if (!$saveRes->isSuccess) return Result::failure("Erro crítico ao salvar Usuário Master.", 500);

		return Result::success(null, "Usuário Master criado com sucesso. E-mail: {$masterEmail} | Senha: {$masterPassword}");

	}

}