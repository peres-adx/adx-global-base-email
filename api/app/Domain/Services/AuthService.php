<?php

namespace App\Domain\Services;

use App\Domain\Common\Result;
use App\Domain\DTOs\Auth\{AuthInputDTO, AuthOutputDTO};
use App\Domain\Entities\User;
use App\Domain\Repositories\Users\IUserRepository;
use App\Domain\ValueObjects\Email;
use App\Infrastructure\Auth\JwtHandler;

class AuthService
{

	private ?User $authenticatedUser = null;
	
	public function __construct(protected IUserRepository $repository) {}

	public function authenticate(AuthInputDTO $dto): Result
	{

		$message = "E-mail ou senha incorretos";

		$emailRes = Email::create($dto->email);
		if (!$emailRes->isSuccess) return Result::failure($message, 202);

		$userRes = $this->repository->listByEmailAuth($emailRes->data);
		if (!$userRes->isSuccess) return Result::failure($message, 202);

		$user = User::restore($userRes->data);
		if (!$user->getPassword()->verify($dto->password)) return Result::failure($message, 202);

		$repoClass    = get_class($this->repository);
		$parts        = explode('\\', $repoClass);
		$activeEngine = $parts[3] ?? env('DB_ENGINE', 'MySQL');

		$accessToken  = $this->generateToken($user, $activeEngine);

		return Result::success(
			new AuthOutputDTO(
				accessToken: $accessToken,
				user: [
					'id'			=> (string) $user->getId(),
					'name'		=> $user->getName(),
					'email'		=> (string) $user->getEmail(),
					'engine'	=> $activeEngine
				]
			),
			"Login realizado com sucesso"
		);

	}

	private function generateToken(User $user, string $engine): string
	{

		$payload = [
			'iss'   => env('JWT_ISSUER'),
			'aud'   => env('JWT_AUDIENCE'),
			'iat'   => time(),
			'exp'   => time() + (int) env('JWT_EXPIRES_IN', 86400),
			'sub'   => (string) $user->getId(),
			'ver'   => (int) $user->getTokenVersion(),
			'name'  => (string) $user->getName(),
			'email' => (string) $user->getEmail(),
			'role'  => 'admin',
			'db'    => $engine
		];

		return JwtHandler::encode($payload);

	}

	public function validateToken(string $token): Result
	{
		if (empty($token)) return Result::failure("O Token é obrigatório");
		return JwtHandler::decode($token);
	}

	public function setAuthenticatedUser(User $user): void   { $this->authenticatedUser = $user; }
	public function user(): ?User                            { return $this->authenticatedUser;  }
	public function id(): ?string                            { return $this->authenticatedUser ? (string) $this->authenticatedUser->getId() : null; }

}