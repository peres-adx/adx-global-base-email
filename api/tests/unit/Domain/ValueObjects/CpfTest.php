<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Cpf;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 * @covers \App\Domain\ValueObjects\Cpf
 */

final class CpfTest extends CIUnitTestCase
{

	public function testDeveAceitarCpfValidoComESemMascara(): void
	{

		$cpfs = ['93718225115', '937.182.251-15'];

		foreach ($cpfs as $input) {
			$result = Cpf::create($input);
			$this->assertTrue($result->isSuccess, "Falhou ao validar CPF válido: {$input}. Erro: " . ($result->error ?? ''));
			$this->assertInstanceOf(Cpf::class, $result->data);
		}

	}

	public function testDeveFalharComCpfMatematicamenteInvalido(): void
	{

		$cpf				= '12345678900';
		$result			= Cpf::create($cpf);

		$this->assertFalse($result->isSuccess);
		$this->assertEquals("CPF '{$cpf}' é numericamente inválido.", $result->error);

	}

	public function testDeveFalharComCpfDeTamanhoErrado(): void
	{

		$cpf				= '123.456.789-0'; // Faltando 1 dígito
		$result			= Cpf::create($cpf);

		$this->assertFalse($result->isSuccess);
		$this->assertEquals("CPF '{$cpf}' possui formato inválido.", $result->error);

	}

	public function testDeveFalharComCpfDeNumerosRepetidos(): void
	{

		$cpf				= '11111111111';
		$result			= Cpf::create($cpf);

		$this->assertFalse($result->isSuccess);
		$this->assertEquals("CPF '{$cpf}' possui formato inválido.", $result->error);

	}

}