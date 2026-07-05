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
		private readonly IUserRepository        $repository,
		private readonly IUserTokenRepository   $tokenRepository
	) {}

	public function listAll(): Result
	{
		return $this->repository->listAll();
	}

	public function listById(string $id): Result
	{
		if (!($uuidRes = Uuid::create($id))->isSuccess) return $uuidRes;
		return $this->repository->listById($uuidRes->data);
	}

	public function listByEmail(string $email): Result
	{
		if (!($emailRes = Email::create($email))->isSuccess)  return $emailRes;
		return $this->repository->listByEmail($emailRes->data);
	}

	public function register(UserInputDTO $dto): Result
	{

		if (empty($dto->cpf)) return Result::failure("O CPF é obrigatório.", 202);

		if (!($emailRes = Email::create($dto->email))->isSuccess)																			return $emailRes;
		if (!($cpfRes   = ($dto->cpf ? Cpf::create($dto->cpf) : Result::success(null)))->isSuccess)		return $cpfRes;
		if ($this->repository->exists($emailRes->data, $cpfRes->data))																return Result::failure("E-mail ou CPF já cadastrado.", 202);

		$user		= User::create($dto, $emailRes->data, null, $cpfRes->data);
		$result	= $this->repository->register($user);

		if (!$result->isSuccess) return $result;

		$data		= $result->data;
		$data['id'] = strtoupper(str_replace('-', '', $data['id']));
		unset($data['password']);

		$token = bin2hex(random_bytes(32));
		if (!$this->tokenRepository->saveInvite($user->getId(), $token, $user->getTokenVersion())) return Result::failure("Erro ao gerar convite.", 500);

		$this->sendWelcomeEmail($user, $token);

		return Result::success($data, "Usuário criado com sucesso. E-mail de ativação enviado.", 201);

	}

	public function update(UserInputDTO $dto, string $id): Result
	{

		if (empty($dto->cpf)) return Result::failure("O CPF é obrigatório para realizar a atualização.", 202);

		if (!($uuidRes  = Uuid::create($id))->isSuccess)																							return $uuidRes;
		if (!($emailRes = Email::create($dto->email))->isSuccess)																			return $emailRes;
		if (!($cpfRes   = ($dto->cpf ? Cpf::create($dto->cpf) : Result::success(null)))->isSuccess)		return $cpfRes;

		$userCheck = $this->repository->listById($uuidRes->data);
		if (!$userCheck->isSuccess) return Result::failure("Usuário não localizado.");

		if ($this->repository->exists($emailRes->data, $cpfRes->data, $uuidRes->data))								return Result::failure("E-mail ou CPF já cadastrado para outro usuário.", 202);

		$result = $this->repository->listById($uuidRes->data);
		if (!$result->isSuccess) return $result;

		$user = User::restore($result->data);
		$user->updateFromDTO($dto, $emailRes->data, $cpfRes->data);

		return $this->repository->update($user);

	}

	public function delete(string $id, string $loggedUserId): Result
	{

		if (!($uuidRes = Uuid::create($id))->isSuccess) return $uuidRes;

		$targetId   = strtoupper(str_replace('-', '', $id));
		$currentId  = strtoupper(str_replace('-', '', $loggedUserId));

		if ($targetId === $currentId)                       return Result::failure("Não é permitido excluir o próprio usuário logado.", 202);
		if (!$this->repository->existsById($uuidRes->data)) return Result::failure("Usuário não encontrado.", 202);

		return $this->repository->delete($uuidRes->data);

	}

	public function createMasterUser(): Result
	{

		$masterEmail		= 'master@adx-global-base.com';
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

		$cpfRes   = Cpf::create($dto->cpf);
		$passRes  = Password::create($dto->password);
		$user     = User::create($dto, $emailRes->data, $passRes->data, $cpfRes->data);

		$saveRes = $this->repository->register($user);
		if (!$saveRes->isSuccess) return Result::failure("Erro crítico ao salvar Usuário Master.", 500);

		return Result::success(null, "Usuário Master criado com sucesso. E-mail: {$masterEmail} | Senha: {$masterPassword}");

	}

	public function setupPassword(string $token, string $newPassword): Result
	{

		$tokenData = $this->tokenRepository->findByToken($token);

		if (!$tokenData)            return Result::failure("Link inválido ou já utilizado.", 202);
		if ($tokenData->isExpired)  return Result::failure("Link expirado.", 202);

		if (!($uuidRes = Uuid::create($tokenData->userId))->isSuccess) return $uuidRes;

		$userRes = $this->repository->listById($uuidRes->data);
		if (!$userRes->isSuccess) return Result::failure("Usuário não localizado.", 202);

		$user = User::restore($userRes->data);
		if ($user->getTokenVersion() !== (int) $tokenData->version) return Result::failure("Este link de ativação foi revogado.", 202);

		$passwordRes = Password::create($newPassword);
		if (!$passwordRes->isSuccess) return $passwordRes;

		$user->setNewPassword($passwordRes->data);
		$user->incrementTokenVersion(); 

		if (!$this->repository->update($user)->isSuccess) return Result::failure("Erro ao salvar senha.", 500);

		$this->tokenRepository->markAsUsed($token);

		return Result::success(null, "Senha definida com sucesso!");

	}

	private function sendWelcomeEmail(User $user, string $token): void
	{

		$email = Services::email();
		$email->clear();

		$email->setTo((string)$user->getEmail());
		$email->setSubject('Ative sua conta - ADX API');

		$currentHeader = service('request')->header('X-Database-Engine')?->getValue();
		$rawEngine     = !empty($currentHeader) && $currentHeader !== 'SELECIONE O BANCO DE DADOS' ? trim($currentHeader) : env('DB_ENGINE', 'SQLite');
		
		$dbQueryParam = match(strtoupper(str_replace(' ', '', $rawEngine))) {
			'SQLSERVER' => 'mssql',
			'SQLITE'    => 'sqlite',
			default     => 'mysql'
		};

		$baseUrl	= env('app.frontendURL') ?? 'http://localhost:8080/adx-global-base/docs/';
		$link			= rtrim($baseUrl, '/') . "/?token={$token}&db={$dbQueryParam}";

		$body = "
			<div style='font-family: Arial, sans-serif; color: #222; max-width: 600px; margin: 0 auto; padding: 20px;'>
				<h2 style='color: #111; font-weight: 700;'>Olá, {$user->getName()},</h2>
				<p>Sua conta foi criada com sucesso na <strong>ADX BASE API</strong>.</p>
				<p>Para definir sua senha e acessar a plataforma, clique no botão abaixo:</p>
				<div style='margin: 35px 0;'>
					<a href='{$link}' style='background-color: #198754; color: #FFF; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; inline-block: true; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
						Definir Minha Senha
					</a>
				</div>
				<p style='font-size: 12px; color: #666; margin-top: 20px;'>
					Se o botão não funcionar, copie e cole o link abaixo no seu navegador:<br />
					<a href='{$link}' style='color: #198754;'>{$link}</a>
				</p>
				<hr style='border: 0; border-top: 1px solid #EAEAEA; margin: 30px 0;'>
				<p style='font-size: 11px; color: #999; letter-spacing: 0.5px;'>ADX BASE API por Rafael Peres (ADX)</p>
			</div>
		";

		$email->setMessage($body);

		if ($email->send()) return;

		log_message('error', 'Falha SMTP para: ' . $user->getEmail());
		log_message('error', $email->printDebugger(['headers', 'subject']));

	}
	
}