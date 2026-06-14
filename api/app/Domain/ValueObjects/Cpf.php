<?php

namespace App\Domain\ValueObjects;

use App\Domain\Common\Result;

final class Cpf
{

	private function __construct(public readonly string $value) {}

	public static function create(string $cpf): Result
	{

		$cleanCpf = preg_replace('/[^0-9]/', '', $cpf);

    if (empty($cleanCpf)) return Result::failure("O CPF não pode estar vazio.");
    if (strlen($cleanCpf) !== 11 || preg_match('/(\d)\1{10}/', $cleanCpf)) return Result::failure("CPF '{$cpf}' possui formato inválido.");
    if (!self::validateDigits($cleanCpf)) return Result::failure("CPF '{$cpf}' é numericamente inválido.");

    return Result::success(new self($cleanCpf));

  }

	private static function validateDigits(string $cpf): bool
	{

    for ($t = 9; $t < 11; $t++) {
      for ($d = 0, $c = 0; $c < $t; $c++) {
        $d += $cpf[$c] * (($t + 1) - $c);
      }
      $d = ((10 * $d) % 11) % 10;
      if ($cpf[$c] != $d) return false;
    }

    return true;

  }

	public function format():					string	{ return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $this->value);	}
	public function __toString():			string	{	return $this->value;	}

}