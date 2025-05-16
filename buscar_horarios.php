<?php
include 'conexao.php';

if (!isset($_GET['data'])) {
    echo "Data não informada!";
    exit;
}

$data_selecionada = $_GET['data'];

$hora_inicio = strtotime("09:00");
$hora_fim = strtotime("18:00");
$intervalo = 40 * 60; // 40 minutos

// Buscar horários agendados
$horarios_agendados = array();
$sql = "SELECT datahora FROM agendamentos WHERE DATE(datahora) = '$data_selecionada'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $horarios_agendados[] = date('H:i', strtotime($row['datahora']));
    }
}

// Gerar os horários disponíveis
for ($i = $hora_inicio; $i <= $hora_fim; $i += $intervalo) {
    $horario_str = date("H:i", $i);
    $classe = "horario";
    if (in_array($horario_str, $horarios_agendados)) {
        $classe .= " indisponivel";
    }
    echo "<div class='$classe' data-hora='$horario_str' onclick=\"selecionarHorario(this)\">$horario_str</div>";
}
?>
