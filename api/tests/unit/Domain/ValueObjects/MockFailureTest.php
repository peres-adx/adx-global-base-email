<?php

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;

class MockFailureTest extends TestCase
{

	/**
	 * Força uma falha de asserção simples para checar o layout do relatório.
	 */
	public function testSimularFalhaDeValidacao(): void
	{
		$statusEsperado		= 'success';
		$statusObtido			= 'failed';
		$this->assertSame($statusEsperado, $statusObtido, 'A API retornou status de falha inesperado.');
	}

	/**
	 * Força uma falha matemática para testar o comportamento do dump no report.
	 */
	public function testSimularErroCalculoDígito(): void
	{
		$digitoCalculado	= 5;
		$digitoEsperado		= 9;
		$this->assertEquals($digitoEsperado, $digitoCalculado, 'O dígito verificador calculado matematicamente está incorreto.');
	}

}