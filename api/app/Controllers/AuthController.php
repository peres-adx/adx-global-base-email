<?php

namespace App\Controllers;

use App\Domain\DTOs\Auth\AuthInputDTO;
use App\Traits\ResponseHandler;
use Config\Services;

class AuthController extends BaseController
{

	use ResponseHandler;

	public function login()
	{

		$inputData = $this->request->getJSON(true) ?? [];
		$dto       = AuthInputDTO::fromArray($inputData);

		return $this->sendResult(Services::authService(false)->authenticate($dto));

	}

}