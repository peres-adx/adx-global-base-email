<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Password;
use CodeIgniter\Test\CIUnitTestCase;

final class PasswordTest extends CIUnitTestCase
{

	/**
	 * Testa a criação e validação de senhas.
	 * @dataProvider passwordDataProvider
	 */
	public function testValidacaoDeSenha(string $input, bool $expectedSuccess, ?string $expectedError = null): void
	{

		$result = Password::create($input);

		$this->assertSame($expectedSuccess, $result->isSuccess);
		
		if (!$expectedSuccess) {
			$this->assertEquals($expectedError, $result->error);
			return;
		}

		$this->assertInstanceOf(Password::class, $result->data);
		$this->assertTrue($result->data->verify($input));

	}

	/**
	 * Data Provider para centralizar todos os cenários de criação de senha.
	 */
	public static function passwordDataProvider(): array
	{
		return [
			'Senha válida'      => ['Senha@Mestra2026', true],
			'Senha vazia'       => ['', false, 'A senha é obrigatória.'],
			'Senha curta'       => ['12345', false, 'A senha deve ter no mínimo 6 caracteres.'],
		];
	}

	/**
	 * Testa a reconstituição a partir de um Hash (Banco de Dados).
	 * @dataProvider hashDataProvider
	 */
	public function testReconstituicaoDeHash(string $hash, bool $expectedSuccess, ?string $expectedError = null): void
	{

		$result = Password::fromHash($hash);

		$this->assertSame($expectedSuccess, $result->isSuccess);

		if (!$expectedSuccess) {
			$this->assertEquals($expectedError, $result->error);
			return;
		}

		$this->assertEquals($hash, (string) $result->data);

	}

	public static function hashDataProvider(): array
	{
		return [
			'Hash válido' => [password_hash('123456', PASSWORD_ARGON2ID), true],
			'Hash vazio'  => ['', false, 'Hash de senha inválido.'],
		];
	}

}