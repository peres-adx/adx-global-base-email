<?php
header('Content-Type: text/plain; charset=utf-8');

// Dados idênticos ao seu .env
$serverName = ".\SQLServer"; 
$connectionOptions = [
    "Database" => "adx-global-base",
    "Uid"      => "sa",
    "PWD"      => "!Raf450pe",
    "CharacterSet" => "UTF-8",
    // Configurações que você viu no SSMS
    "Encrypt" => true,
    "TrustServerCertificate" => true,
];

echo "Iniciando teste de conexão SQLSRV...\n";
echo "Servidor: $serverName\n";
echo "-----------------------------------\n";

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo "FALHA NA CONEXÃO!\n";
    if (($errors = sqlsrv_errors()) != null) {
        foreach ($errors as $error) {
            echo "SQLSTATE: ".$error['SQLSTATE']."\n";
            echo "Código: ".$error['code']."\n";
            echo "Mensagem: ".mb_convert_encoding($error['message'], 'UTF-8', 'Windows-1252')."\n";
            echo "-----------------------------------\n";
        }
    }
} else {
    echo "SUCESSO! Conexão estabelecida com o SQL Server.\n";
    
    $query = "SELECT @@VERSION as version";
    $stmt = sqlsrv_query($conn, $query);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    echo "Versão do Banco: " . $row['version'] . "\n";
    
    sqlsrv_close($conn);
}