<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barbearia_do_banco"; // Nome do banco de dados que você criou

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>