<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Email;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 * @covers \App\Domain\ValueObjects\Email
 */

final class EmailTest extends CIUnitTestCase
{

	/**
	 * @dataProvider emailDataProvider
	 */
	public function testValidacaoDeEmail(string $input, bool $expectedSuccess, ?string $expectedError = null): void
	{

		$result = Email::create($input);

		$this->assertSame($expectedSuccess, $result->isSuccess);

		if (!$expectedSuccess) {
			$this->assertEquals($expectedError, $result->error);
			return;
		}

		$this->assertInstanceOf(Email::class, $result->data);
		$this->assertEquals($input, (string) $result->data);

	}

	public static function emailDataProvider(): array
	{
		return [
			'E-mail válido Hausti'   => ['rp@hausti.app', true],
			'E-mail com subdomínio'  => ['dev@api.hausti.app', true],
			'E-mail vazio'           => ['', false, 'Um e-mail deve ser fornecido.'],
			'E-mail sem arroba'      => ['rafaelhausti.com.br', false, "O formato do e-mail 'rafaelhausti.com.br' é inválido."],
			'E-mail sem domínio'     => ['rafael@', false, "O formato do e-mail 'rafael@' é inválido."],
			'E-mail apenas espaços'  => ['   ', false, "O formato do e-mail '   ' é inválido."],
			'E-mail inválido'        => ['not-an-email', false, "O formato do e-mail 'not-an-email' é inválido."],
		];
	}

}