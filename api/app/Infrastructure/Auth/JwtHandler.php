<?php

namespace App\Infrastructure\Auth;

use App\Domain\Common\Result;
use Firebase\JWT\{JWT, Key};
use Exception;

final class JwtHandler
{

	private static function getKey():								Key			{	return new Key(env('JWT_SECRET'), 'HS256'); }
	public static function	encode(array $payload):	string	{	return JWT::encode($payload, env('JWT_SECRET'), 'HS256'); }

	public static function	decode(string $token): 	Result
	{
		try {
			$decoded = JWT::decode($token, self::getKey());
			return Result::success($decoded);
		} catch (Exception) { return Result::failure("Token inválido ou expirado"); }
	}

}