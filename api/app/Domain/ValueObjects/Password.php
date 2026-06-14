<?php

namespace App\Domain\ValueObjects;

use App\Domain\Common\Result;

final class Password
{

	private function __construct(private readonly string $hash) {}

	public static function create(string $plain): Result
	{

		if (empty($plain))			return Result::failure("A senha é obrigatória.");
		if (strlen($plain) < 6)	return Result::failure("A senha deve ter no mínimo 6 caracteres.");
		
		$hash = password_hash($plain, PASSWORD_ARGON2ID, [
			'memory_cost' => 65536,
			'time_cost'   => 4,
			'threads'     => 2
		]);

		return Result::success(new self($hash));

	}

	public static function fromHash(string $hash): Result
	{
		if (empty($hash)) return Result::failure("Hash de senha inválido.");
		return Result::success(new self($hash));
	}

	public function verify(string $plain):    bool   { return password_verify($plain, $this->hash); }
	public function getHash():                string { return $this->hash; }
	public function __toString():             string { return $this->hash; }

}