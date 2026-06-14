<?php

namespace App\Domain\Common;

final readonly class BaseMapper
{
	public static function mapList(array $collection, string $dtoClass, string $method = 'fromEntity'): Result
	{
    if (empty($collection)) return Result::success([]);
    $data = array_map(fn($item) => $dtoClass::$method($item), $collection);
    return Result::success($data);
  }
}