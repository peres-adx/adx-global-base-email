<?php

namespace App\Domain\ValueObjects;

use App\Domain\Common\Result;

final class Email
{

	private function __construct(private readonly string $value) {}

	public static function create(string $email): Result
	{

		if (empty($email)) return Result::failure("Um e-mail deve ser fornecido.");
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return Result::failure("O formato do e-mail '{$email}' é inválido.");

		return Result::success(new self($email));

	}

	public function getValue():				string	{	return $this->value; }
	public function __toString():			string	{	return $this->value; }

}