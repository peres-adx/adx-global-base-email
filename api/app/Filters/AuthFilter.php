<?php

namespace App\Filters;

use App\Domain\ValueObjects\{Uuid};
use App\Domain\Entities\User;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\{RequestInterface, ResponseInterface};

use Config\Services;

class AuthFilter implements FilterInterface
{

	public function before(RequestInterface $request, $arguments = null)
	{

		if (strtoupper($request->getMethod()) === 'OPTIONS') return;

		$authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? $request->header('Authorization');
		if (!$authHeader) return $this->fail('Sessão encerrada ou token ausente.');

		$token  = str_ireplace('Bearer ', '', (string)$authHeader);
		$result = Services::authService()->validateToken($token);

		if (!$result->isSuccess) return $this->fail($result->detail ?? 'Token inválido.');

		$decoded = $result->data;
	
		$uuidRes = Uuid::create((string)($decoded->sub ?? ''));
		if (!$uuidRes->isSuccess) return $this->fail('Token com formato de ID inválido.');

		$userRes = Services::userRepository()->listById($uuidRes->data);
		if (!$userRes->isSuccess) return $this->fail('Usuário não encontrado ou conta desativada.');

		$userEntity = User::restore($userRes->data);
		if ((int)($decoded->ver ?? 0) !== (int)$userEntity->getTokenVersion()) return $this->fail('Sessão expirada. Por favor, faça login novamente.');

		Services::authService()->setAuthenticatedUser($userEntity);

	}

	private function fail(string $message): ResponseInterface
	{
		return Services::response()->setJSON([
			'status'  => 202,
			'detail'  => $message
		])->setStatusCode(202);
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) { }

}