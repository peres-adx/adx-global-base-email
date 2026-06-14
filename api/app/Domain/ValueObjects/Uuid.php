<?php

namespace App\Domain\ValueObjects;

use App\Domain\Common\Result;
use Ramsey\Uuid\Uuid as ExternalUuid;

final class Uuid
{

	private function __construct(private readonly string $value) {}

	public static function create(?string $uuid = null): Result
	{

		if ($uuid === null)         return Result::success(new self(self::generateValue()));
		if (!self::isValid($uuid))  return Result::failure("UUID Inválido: {$uuid}", 400);

		return Result::success(new self(strtoupper(str_replace('-', '', $uuid))));

	}

	public static		function next():												string	{ return (string) self::create()->data; }
	public static		function fromBinary(string $binary):		self		{ return new self(strtoupper(bin2hex($binary))); }
	public					function toBinary():										string 	{ return hex2bin($this->value); }
  private static	function isValid(string $uuid):					bool		{ return preg_match('/^[0-9a-f]{8}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{12}$/i', $uuid) === 1; }
  private static	function generateValue():								string	{ return strtoupper(str_replace('-', '', ExternalUuid::uuid4()->toString())); }
  public					function __toString():									string	{ return $this->value; }

}