<?php

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

/**
 * Verifica se um horário está disponível para agendamento, considerando um intervalo de tempo.
 *
 * @param mysqli $conn A conexão com o banco de dados.
 * @param string $datahora_str A string representando a data e hora a verificar (no formato YYYY-MM-DD HH:MM).
 * @param int $intervalo O intervalo em minutos a ser considerado para verificar a disponibilidade.
 * @return bool Retorna true se o horário estiver disponível (nenhum agendamento冲突 dentro do intervalo), false caso contrário.
 */
function horarioDisponivel($conn, $datahora_str, $intervalo) {
    try {
        // Tenta criar um objeto DateTime a partir da string de data e hora fornecida
        $datahora = new DateTime($datahora_str);
    } catch (Exception $e) {
        // Se ocorrer um erro ao criar o objeto DateTime (formato inválido), registra o erro e retorna false
        error_log("Erro ao criar DateTime: " . $e->getMessage() . " - Data/Hora: " . $datahora_str);
        return false; // Ou lançar uma exceção, dependendo do seu tratamento de erros
    }
    // Clona o objeto DateTime inicial e adiciona o intervalo para obter o horário de fim do agendamento
    $datahora_fim = (clone $datahora)->modify('+' . $intervalo . ' minutes');

    // Define a consulta SQL para verificar se existe algum agendamento que se sobrepõe ao horário desejado (incluindo o intervalo)
    // A condição verifica se algum agendamento existente começa antes do final do novo agendamento E termina depois do início do novo agendamento
    $check_sql = "SELECT COUNT(*) FROM agendamentos WHERE
                    (datahora < '" . $datahora_fim->format('Y-m-d H:i:s') . "' AND DATE_ADD(datahora, INTERVAL $intervalo MINUTE) > '" . $datahora->format('Y-m-d H:i:s') . "')";

    // Executa a consulta SQL
    $check_result = $conn->query($check_sql);
    // Obtém o número de linhas retornadas pela consulta (quantos agendamentos conflitantes foram encontrados)
    $check_count = $check_result->fetch_row()[0];

    // Retorna true se nenhum agendamento conflitante foi encontrado (count é 0), false caso contrário
    return $check_count == 0;
}

// Verifica se o método da requisição é POST (ou seja, se o formulário foi enviado)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário enviados pelo método POST
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $servico = $_POST['servico'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];

    // Concatena a data e a hora para formar uma string de data e hora no formato adequado
     // Define o intervalo de tempo para a verificação de disponibilidade (em minutos)
    $datahora_str = $data . ' ' . $hora . ':00';
    $intervalo = 40;

    // Chama a função para verificar se o horário está disponível
 // Se o horário não estiver disponível, exibe um alerta JavaScript e redireciona o usuário de volta ao formulário,
        // mantendo os dados já preenchidos para facilitar a escolha de outro horário
    if (!horarioDisponivel($conn, $datahora_str, $intervalo)) {
        echo "<script>alert('Horário não disponível (incluindo intervalo). Por favor, escolha outro horário.'); window.location.href='barbearia_do_corte.php?data=$data&nome=$nome&telefone=$telefone&servico=$servico';</script>";
    } else {
        // Se o horário estiver disponível, tenta formatar a string de data e hora para o formato DATETIME do MySQL
        try {
            $datahora = (new DateTime($datahora_str))->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // Se ocorrer um erro ao formatar a data e hora, registra o erro, exibe um alerta e redireciona o usuário de volta ao formulário
            
            error_log("Erro ao formatar DateTime: " . $e->getMessage() . " - Data/Hora: " . $datahora_str);
            echo "<script>alert('Erro ao processar a data/hora. Por favor, tente novamente.'); window.location.href='barbearia_do_corte.php?data=$data&nome=$nome&telefone=$telefone&servico=$servico';</script>";
            exit; // Importante: interromper a execução para evitar inserção incorreta no banco de dados
        }

        // Define a consulta SQL para inserir os dados do agendamento na tabela 'agendamentos'
        $sql = "INSERT INTO agendamentos (nome, telefone, servico, datahora)
                    VALUES ('$nome', '$telefone', '$servico', '$datahora')";

        // Executa a consulta SQL de inserção
        // Se a inserção for bem-sucedida, exibe um alerta de sucesso e redireciona o usuário de volta ao formulário,
        // mantendo a data selecionada para facilitar novos agendamentos na mesma data
        if ($conn->query($sql) === TRUE) {
            
            echo "<script>alert('Agendamento realizado com sucesso!'); window.location.href='barbearia_do_corte.php?data=$data';</script>";
        } else {
            // Se ocorrer um erro durante a inserção, exibe uma mensagem de erro detalhada
            echo "Erro: " . $sql . "<br>" . $conn->error;
        }
    }

    // Fecha a conexão com o banco de dados
    $conn->close();
}
?>