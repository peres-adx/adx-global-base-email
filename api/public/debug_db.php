<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h3>Diagnóstico de Ambiente SQLite</h3>";
echo "<b>PHP Version:</b> " . PHP_VERSION . "<br>";
echo "<b>SQLite3 Class:</b> " . (class_exists('SQLite3') ? '✅ Carregada' : '❌ NÃO CARREGADA') . "<br>";
echo "<b>PDO SQLite Driver:</b> " . (in_array('sqlite', PDO::getAvailableDrivers()) ? '✅ Carregada' : '❌ NÃO CARREGADA') . "<br>";

$path = __DIR__ . '/../writable/database/adx-test.sqlite';
echo "<b>Tentando criar arquivo em:</b> $path <br>";

try {
    $db = new SQLite3($path);
    $db->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER)");
    echo "<b>Escrita física:</b> ✅ SUCESSO! O arquivo foi criado e a tabela gerada.";
} catch (Exception $e) {
    echo "<b>Escrita física:</b> ❌ FALHOU! Erro: " . $e->getMessage();
}
