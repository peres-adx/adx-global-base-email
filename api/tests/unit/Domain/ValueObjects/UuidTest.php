<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Uuid;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 * @covers \App\Domain\ValueObjects\Uuid
 */
final class UuidTest extends CIUnitTestCase
{

	/**
	 * Testa a criação e validação de UUIDs.
	 * @dataProvider uuidDataProvider
	 */
	public function testValidacaoDeUuid(string $input, bool $expectedSuccess, ?string $expectedError = null): void
	{

		$result = Uuid::create($input);

		$this->assertSame($expectedSuccess, $result->isSuccess);

		if (!$expectedSuccess) {
			$this->assertEquals($expectedError, $result->error);
			return;
		}

		$this->assertInstanceOf(Uuid::class, $result->data);
		
		// O output do VO deve ser sanitizado (Upper, sem hífens, 32 caracteres)
		$expectedSanitized = strtoupper(str_replace('-', '', $input));
		$this->assertEquals($expectedSanitized, (string) $result->data);

	}

	/**
	 * Data Provider para centralizar os cenários de criação e validação de formato.
	 */
	public static function uuidDataProvider(): array
	{
		return [
			'UUID Válido Com Hifens'    => ['550e8400-e29b-41d4-a716-446655440000', true],
			'UUID Válido Sem Hifens'    => ['550E8400E29B41D4A716446655440000', true],
			'UUID Inválido Formato'     => ['uuid-totalmente-errado', false, 'UUID Inválido: uuid-totalmente-errado'],
			'UUID Vazio'                => ['', false, 'UUID Inválido: '],
		];
	}

	/**
	 * Testa a geração dinâmica (Caminho Feliz sem passar string).
	 */
	public function testGeracaoDinamicaDeUuid(): void
	{

		$result = Uuid::create();

		$this->assertTrue($result->isSuccess);
		$this->assertInstanceOf(Uuid::class, $result->data);
		$this->assertEquals(32, strlen((string) $result->data));

	}

	/**
	 * Testa a reconstituição a partir de binário (Banco de Dados Otimizado).
	 */
	public function testReconstituicaoDeBinario(): void
	{

		// Cria um UUID válido e extrai seu binário (16 bytes)
		$originalUuid	= Uuid::create()->data;
		$binary				= $originalUuid->toBinary();
		$result				= Uuid::fromBinary($binary);

		$this->assertInstanceOf(Uuid::class, $result);
		$this->assertEquals(16, strlen($binary));
		$this->assertEquals((string) $originalUuid, (string) $result);

	}

}