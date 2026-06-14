<?php

namespace App\Domain\Services;

use App\Domain\Common\Result;
use Firebase\JWT\{JWT, Key};
use Exception;

class TokenService
{
	public static function decode(string $token): Result
	{
		try {
			$decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
			return Result::success($decoded);
		} catch (Exception) { return Result::failure("Token inválido ou expirado"); }
	}
}