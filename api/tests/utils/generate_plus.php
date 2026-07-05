<?php

/**
 * TDDash — Dynamic Test Report Generator
 * Architect: Rafael Peres
 */

$xmlFile        = __DIR__ . '/../report/results.xml';
$outputFile     = __DIR__ . '/../report/index.html';
$cssPath        = '../utils/styles/simplePress.css';
$tddashCssPath  = '../audit/assets/styles/tddash.css';

if (!file_exists($xmlFile)) die("ERRO CRÍTICO: O arquivo results.xml não foi encontrado em: $xmlFile\n");

$xml = simplexml_load_file($xmlFile);

$testsuitesAttr = $xml->xpath('/testsuites/testsuite')[0]	?? $xml;
$totalTests     = (int)($testsuitesAttr['tests']					?? 0);
$failures       = (int)($testsuitesAttr['failures']				?? 0) + (int)($testsuitesAttr['errors'] ?? 0);
$successes      = $totalTests - $failures;

// Dicionário de UX: Mapeia métodos técnicos para especificações de negócio
$dictionary = [
	// CpfTest
	'testDeveAceitarCpfValidoComESemMascara' => [
		'title'     => 'CPF válido (com e sem caracteres de máscara)',
		'intent'    => 'fluxo',
		'expected'  => 'O sistema deve aceitar o documento e prosseguir sem restrições.'
	],
	'testDeveFalharComCpfMatematicamenteInvalido' => [
		'title'     => 'CPF com dígitos verificadores inválidos',
		'intent'    => 'bloqueio',
		'expected'  => 'O sistema deve recalcular os dígitos, detectar a inconsistência matemática e impedir o avanço.'
	],
	'testDeveFalharComCpfDeTamanhoErrado' => [
		'title'     => 'CPF com quantidade incorreta de caracteres',
		'intent'    => 'bloqueio',
		'expected'  => 'O sistema deve rejeitar de forma imediata qualquer entrada que não possua exatamente 11 dígitos.'
	],
	'testDeveFalharComCpfDeNumerosRepetidos' => [
		'title'     => 'CPF composto por números repetidos / sequências',
		'intent'    => 'bloqueio',
		'expected'  => 'O sistema deve identificar e bloquear fraudes conhecidas por repetição (ex: 111.111.111-11).'
	],
	// EmailTest
	'testValidacaoDeEmail' => [
		'title'     => 'Validação de formato de e-mail corporativo e estruturas',
		'intent'    => 'fluxo',
		'expected'  => 'O sistema deve validar a RFC do e-mail e certificar a existência de nós de domínio legítimos.'
	]
];

function toSnakeCase(string $input): string
{
	$value = preg_replace('/[A-Z]/', '_$0', $input);
	$value = strtolower($value);
	$value = ltrim($value, '_');

	return str_replace([' ', '-'], '_', $value);
}

ob_start();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>TDD Report — Global Tests</title>
	<link rel="stylesheet" href="<?= $cssPath ?>" />
	<link rel="stylesheet" href="<?= $tddashCssPath ?>" />
</head>
<body class="theme-dark">

	<div class="theme-wrapper">

		<header class="container text-center mt-20">
			<h1 class="text-upper">TDD <span style="color: var(--brand-success)">API</span></h1>
			<p>Software Architecture Integrity Report — v2.6</p>
		</header>

		<main class="container mt-20 pt-20">

			<div class="row main-cards">

				<div class="col-12 col-lg-4">
					<div class="card text-center">
						<h3 class="text-upper">Total</h3>
						<span class="stat-number"><?= $totalTests ?></span>
					</div>
				</div>
				<div class="col-12 col-lg-4">
					<div class="card text-center">
						<h3 class="text-upper" style="color: var(--brand-success)">Success</h3>
						<span class="stat-number" style="color: var(--brand-success)"><?= $successes ?></span>
					</div>
				</div>
				<div class="col-12 col-lg-4">
					<div class="card text-center">
						<h3 class="text-upper" style="color: var(--brand-error)">Failures</h3>
						<span class="stat-number" style="color: var(--brand-error)"><?= $failures ?></span>
					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-12">

					<div class="ux-guide-banner">
						<h4>Documento de Auditoria de Testes Unitários</h4>
						<p>
							Este documento audita a integridade das regras de negócio do software. O status indicador 
							<strong style="color: var(--brand-success)">PASS</strong> comprova que o sistema se comportou 
							<strong>exatamente como esperado</strong> pelo negócio.
						</p>
					</div>

				</div>

				<div class="col-12">

					<div class="dashboard-filters">
						<button class="filter-btn active" data-filter="all">🔵 Todos <span class="badge-count"><?= $totalTests ?></span></button>
						<button class="filter-btn" data-filter="pass">🟢 Pass <span class="badge-count btn-pass"><?= $successes ?></span></button>
						<button class="filter-btn" data-filter="fail">🔴 Fail <span class="badge-count btn-fail"><?= $failures ?></span></button>
					</div>

				</div>

			</div>

			<?php
				$suites = $xml->xpath('//testsuite[@file]');
				foreach ($suites as $suite) {
			?>

			<section>
				<h2 class="text-upper" style="font-size: 0.9rem; color: #4B89CF; letter-spacing: 1px;">
					<p>Suite: <?= basename(str_replace('\\', '/', (string)$suite['name'])) ?></p>
				</h2>

				<?php

					$cases = $suite->xpath('.//testcase');

					foreach ($cases as $case) {

						$rawName = (string)$case['name'];

						$methodKey = $rawName;
						if (strpos($rawName, ' with data set ') !== false) $methodKey = explode(' with data set ', $rawName)[0];

						$title			= $dictionary[$methodKey]['title']		?? ucwords(strtolower(trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $rawName))));
						$intent			= $dictionary[$methodKey]['intent']		?? 'fluxo';
						$expected		= $dictionary[$methodKey]['expected'] ?? 'O sistema deve processar a operação respeitando as restrições arquiteturais.';

						if (strpos($rawName, ' with data set ') !== false) {
							preg_match('/"([^"]+)"/', $rawName, $matches);
							$scenario	= $matches[1] ?? 'Cenário customizado';
							$title		= explode(' — Cenário:', $title)[0] . " — Cenário: " . $scenario;
						}

						$isFail				= isset($case->failure) || isset($case->error);
						$statusClass	= $isFail ? 'fail' : 'pass';
						$statusText		= $isFail ? 'FAIL' : 'PASS';
						$rowStyle			= $isFail ? 'style="border-left: 4px solid var(--brand-error);"' : '';
						$badgeType		= ($intent === 'bloqueio') ? 'fail-type' : 'success-type';
						$badgeLabel		= ($intent === 'bloqueio') ? 'Validação de bloqueio' : 'Validação de fluxo';

				?>

				<div class="test-row" data-status="<?= $statusClass ?>" <?= $rowStyle ?>>

					<div class="test-details">

						<div class="test-title-wrapper">
							<span class="test-title"><?= htmlspecialchars($title); ?></span>
							<span class="intent-badge <?= $badgeType ?>"><?= $badgeLabel ?></span>
						</div>

						<div class="test-guide">
							<strong>Status esperado:</strong> <?= htmlspecialchars($expected); ?>
						</div>

						<div class="test-technical">
							<code><?= htmlspecialchars($rawName); ?></code>
							<?php if ($isFail) { ?>
							<div class="failure-box"><?= htmlspecialchars((string)($case->failure ?? $case->error)); ?></div>
							<?php } ?>
						</div>

					</div>

					<div class="test-status">
						<span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
					</div>

				</div>

				<?php
						if ($isFail) {
							$message = isset($case->failure) ? (string)$case->failure['message'] : (string)$case->error['message'];
							echo "<div class='failure-details'><strong>Detalhes da Falha:</strong> " . htmlspecialchars($message) . "</div>";
						}
					}
				?>
			</section>
			<?php
				}
			?>

		</main>

		<footer class="container text-center mt-20 pt-20">
			<p>© 2026 ADX | TDD Architected by <strong>Rafael Peres</strong></p>
		</footer>

	</div>

<script type="text/javascript">

	// document.addEventListener('DOMContentLoaded', () => {

	// 	const buttons		= document.querySelectorAll('.filter-btn');
	// 	const rows			= document.querySelectorAll('.test-row');
	// 	const sections	= document.querySelectorAll('main section');

	// 	buttons.forEach(button => {

	// 		button.addEventListener('click', () => {

	// 			buttons.forEach(btn => btn.classList.remove('active'));
	// 			button.classList.add('active');

	// 			const filterValue = button.getAttribute('data-filter');

	// 			rows.forEach(row => {
	// 				const status = row.getAttribute('data-status');
	// 				if (filterValue === 'all' || status === filterValue) {
	// 					row.classList.remove('hide-by-filter');
	// 				} else {
	// 					row.classList.add('hide-by-filter');
	// 				}
	// 			});

	// 			sections.forEach(section => {

	// 				const totalRows		= section.querySelectorAll('.test-row').length;
	// 				const hiddenRows	= section.querySelectorAll('.test-row.hide-by-filter').length;

	// 				if (totalRows === hiddenRows) {
	// 					section.classList.add('hide-by-filter');
	// 				} else {
	// 					section.classList.remove('hide-by-filter');
	// 				}

	// 			});

	// 		});

	// 	});

	// });

	document.addEventListener('DOMContentLoaded', () => {

		const buttons  = document.querySelectorAll('.filter-btn');
		const rows     = document.querySelectorAll('.test-row');
		const sections = document.querySelectorAll('main section');

		buttons.forEach(button => {

			button.addEventListener('click', () => {

				buttons.forEach(btn => btn.classList.remove('active'));
				button.classList.add('active');

				const filterValue = button.getAttribute('data-filter');

				rows.forEach(row => {

					const status	= row.getAttribute('data-status');
					const isMatch	= (filterValue === 'all' || status === filterValue);

					row.classList.add('hide-by-filter');
					if (isMatch) row.classList.remove('hide-by-filter');

				});

				sections.forEach(section => {

					const total		= section.querySelectorAll('.test-row').length;
					const hidden	= section.querySelectorAll('.test-row.hide-by-filter').length;
					
					section.classList.remove('hide-by-filter');
					if (total === hidden) section.classList.add('hide-by-filter');

				});

			});

		});

	});

</script>
</body>
</html>

<?php

$html = ob_get_clean();
file_put_contents($outputFile, $html);
echo "[+] TDDash Interno gerado com sucesso em: " . realpath($outputFile) . "\n";