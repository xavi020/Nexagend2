<?php
// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php';

// Define a senha secreta para acessar a lista de agendamentos
$senhaSecreta = "123";
// Inicializa uma variável para controlar se o acesso foi liberado
$acessoLiberado = false;
// Inicializa uma variável para armazenar o resultado da consulta ao banco de dados
$result = null;

// Verifica se o método da requisição é POST (quando o formulário de senha é enviado)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém a senha digitada pelo usuário do campo 'senha' no formulário
    $senhaDigitada = $_POST['senha'];

    // Compara a senha digitada com a senha secreta
    if ($senhaDigitada === $senhaSecreta) {
        // Se as senhas coincidem, define a variável de acesso como true
        $acessoLiberado = true;
        // Define a consulta SQL para selecionar todos os registros da tabela 'agendamentos'
        $sql = "SELECT * FROM agendamentos";
        // Executa a consulta SQL no banco de dados e armazena o resultado na variável '$result'
        $result = $conn->query($sql);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ver Agendamentos - Barbearia</title>
    <link rel="stylesheet" href="stylee.css">
</head>
<body>
    <div class="container">
        <h1>Visualizar Agendamentos</h1>

        <?php if (!$acessoLiberado): ?>
            <form method="POST">
                <label for="senha">Digite a senha:</label>
                <input type="password" name="senha" id="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
        <?php else: ?>
            <?php if ($result && $result->num_rows > 0): ?>
                <h2>Lista de Agendamentos</h2>
                <ul>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <li>
                            <strong>Nome:</strong> <?php echo htmlspecialchars($row['nome']); ?><br>
                            <strong>Telefone:</strong> <?php echo htmlspecialchars($row['telefone']); ?><br>
                            <strong>Serviço:</strong> <?php echo htmlspecialchars($row['servico']); ?><br>
                            <strong>Data e Hora:</strong> <?php echo htmlspecialchars($row['datahora']); ?><br>
                        </li>
                        <hr>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nenhum agendamento encontrado.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>