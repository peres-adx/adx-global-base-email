<?php

namespace App\Traits;

use App\Domain\Common\Result;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

trait ResponseHandler
{

  protected function sendResponse(int $status, mixed $detail, array $extraData = []): ResponseInterface
  {

		$response = ['status' => $status, 'detail' => $detail];
    if (!empty($extraData)) $response = array_merge($response, $extraData);

		return Services::response()->setJSON($response)->setStatusCode($status);

  }

	protected function sendResult(Result $result, int $successCode = 200, int $failureCode = 202): ResponseInterface
	{

		if (!$result->isSuccess) return $this->sendResponse($result->code ?? $failureCode, $result->error);

		$response = [
			'status' => $successCode,
			'detail' => $result->message ?? "Operação realizada com sucesso"
		];

		if (!empty($result->data)) $response['data'] = $result->data;

		return Services::response()->setJSON($response)->setStatusCode($successCode);

	}

}