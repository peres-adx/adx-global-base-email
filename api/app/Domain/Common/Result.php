<?php

namespace App\Domain\Common;

final readonly class Result
{

  private function __construct(
    public bool     $isSuccess,
    public mixed    $data    = null,
    public ?string  $message = null,
    public ?string  $error   = null,
    public int      $status  = 200
  ) {}

  public static function success(mixed $data = null, ?string $message = null, int $status = 200): self
  {
    return new self(true, $data, $message, null, $status);
  }

  public static function failure(string $error, int $status = 202): self
  {
    return new self(false, null, null, $error, $status);
  }

}