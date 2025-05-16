<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - Barbearia</title>
    <link rel="stylesheet" href="stylee.css">
    <script>
        // Função JavaScript para selecionar um horário disponível
        function selecionarHorario(elemento) {

            // Remove a classe 'selecionado' de todos os elementos com a classe 'horario'
            document.querySelectorAll('.horario').forEach(el => el.classList.remove('selecionado'));

            // Adiciona a classe 'selecionado' ao elemento clicado para destacar o horário selecionado
            elemento.classList.add('selecionado');

            // Define o valor do campo oculto 'hora' com o valor do atributo 'data-hora' do elemento clicado
            document.getElementById('hora').value = elemento.dataset.hora;
        }

        // Função JavaScript para buscar os horários disponíveis para uma data selecionada
        function buscarHorarios() {

            // Obtém o valor da data selecionada no campo de input com o id 'data'
            var dataSelecionada = document.getElementById('data').value;

            // Se a data selecionada estiver vazia, a função retorna sem fazer nada
            if (dataSelecionada === "") return;

            // Cria um novo objeto XMLHttpRequest para fazer uma requisição assíncrona ao servidor
            var xhr = new XMLHttpRequest();

            // Abre uma requisição GET para o script PHP 'buscar_horarios.php' passando a data selecionada como parâmetro na URL
            xhr.open("GET", "buscar_horarios.php?data=" + dataSelecionada, true);

            // Define a função a ser executada quando o estado da requisição mudar
            xhr.onreadystatechange = function () {

                // Verifica se a requisição foi concluída (readyState === 4) e se o status da resposta foi OK (status === 200)
                if (xhr.readyState === 4 && xhr.status === 200) {

                    // Atualiza o conteúdo do elemento com o id 'horarios-disponiveis' com a resposta do servidor (os horários disponíveis)
                    document.getElementById('horarios-disponiveis').innerHTML = xhr.responseText;
                }
            };

            // Envia a requisição ao servidor
            xhr.send();
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Agende seu horário</h1>
        <form action="processoagendamento.php" method="POST">
            <input type="text" name="nome" placeholder="Seu nome"
                value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
            <input type="text" name="telefone" placeholder="Seu telefone"
                value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>" required>
            <label for="servico">Estilo de Corte:</label><br> <select name="servico" required>
                <option value="">Selecione</option>
                <option value="1" <?php echo (isset($_POST['servico']) && $_POST['servico'] == '1') ? 'selected' : ''; ?>>
                    Corte Degradê - R$35,00</option>
                <option value="2" <?php echo (isset($_POST['servico']) && $_POST['servico'] == '2') ? 'selected' : ''; ?>>
                    Corte Social - R$30,00</option>
                <option value="3" <?php echo (isset($_POST['servico']) && $_POST['servico'] == '3') ? 'selected' : ''; ?>>
                    Sobrancelha - R$5,00</option>
                <option value="4" <?php echo (isset($_POST['servico']) && $_POST['servico'] == '4') ? 'selected' : ''; ?>>
                    Platinado - R$120,00</option>
                <option value="5" <?php echo (isset($_POST['servico']) && $_POST['servico'] == '5') ? 'selected' : ''; ?>>
                    Pigmentação - R$60,00</option>
            </select>

            <label for="data">Data:</label> <input type="date" name="data" id="data" onchange="buscarHorarios()"
                required>
            <label for="hora">Hora:</label> <input type="hidden" name="hora" id="hora" required>
            <div id="horarios-disponiveis"> <?php
            include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados
            
            $hora_inicio = strtotime("09:00"); // Define o horário de início dos agendamentos (9:00)
            $hora_fim = strtotime("18:00"); // Define o horário de fim dos agendamentos (18:00)
            $intervalo = 40 * 60; // Define o intervalo entre os horários em segundos (40 minutos * 60 segundos/minuto)
            $data_selecionada = date('Y-m-d'); // Define a data selecionada como a data atual por padrão
            
            if (isset($_GET['data'])) { // Verifica se uma data foi passada via parâmetro GET (geralmente após a seleção no calendário)
                $data_selecionada = $_GET['data']; // Se sim, atualiza a data selecionada
            }

            $horarios_agendados = array(); // Inicializa um array para armazenar os horários já agendados para a data selecionada
            $sql = "SELECT datahora FROM agendamentos WHERE DATE(datahora) = '$data_selecionada'"; // Query SQL para buscar os horários agendados na data selecionada
            $result = $conn->query($sql); // Executa a query no banco de dados
            if ($result && $result->num_rows > 0) { // Verifica se a query foi bem-sucedida e se há resultados
                while ($row = $result->fetch_assoc()) { // Loop através de cada linha de resultado
                    $horarios_agendados[] = date('H:i', strtotime($row['datahora'])); // Formata a data e hora para apenas a hora e adiciona ao array de horários agendados
                }
            }

            // Loop para gerar os horários disponíveis dentro do intervalo definido
            for ($i = $hora_inicio; $i <= $hora_fim; $i += $intervalo) {
                $horario_str = date("H:i", $i); // Formata o timestamp atual para o formato HH:MM
                $classe = "horario"; // Define a classe CSS padrão para os horários
                if (in_array($horario_str, $horarios_agendados)) { // Verifica se o horário atual já está na lista de horários agendados
                    $classe .= " indisponivel"; // Se estiver agendado, adiciona a classe 'indisponivel'
                }
                echo "<div class='$classe' data-hora='$horario_str' onclick=\"selecionarHorario(this)\">$horario_str</div>"; // Imprime um div para cada horário, com a classe apropriada, o horário como data-hora e a função JavaScript para seleção ao clicar
            }
            ?>
            </div>

            <button type="submit">Agendar</button>
        </form>

</body>

</html>