<?php
// Função para obter os dados do reator filtrados
function obterDadosDoReatorFiltrados($inicio, $fim, $selectedStartDate = null, $selectedEndDate = null)
{
    // Conectar ao banco de dados MariaDB
    $conn = new mysqli("localhost", "root", "123", "dados_reator");

    // Verificar a conexão
    if ($conn->connect_error) {
        die("Erro ao conectar ao banco de dados: " . $conn->connect_error);
    }

    if ($selectedStartDate && $selectedEndDate) {
        // Consulta SQL para selecionar os dados do reator com base nas datas selecionadas
        $sql = "SELECT data, hora, taxa_mesa, fonte, cofre, ar, resina FROM dados WHERE data BETWEEN '$selectedStartDate' AND '$selectedEndDate' ORDER BY data, hora";
    } else {
        // Consulta SQL para obter a data mais recente
        $sql = "SELECT data FROM dados ORDER BY data DESC LIMIT 1";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $latestDate = $row['data'];

        // Consulta SQL para selecionar os dados do reator com base na data mais recente
        $sql = "SELECT data, hora, taxa_mesa, fonte, cofre, ar, resina FROM dados WHERE data = '$latestDate' ORDER BY hora";
    }

    // Executar a consulta SQL
    $result = $conn->query($sql);

    // Inicializar um array para armazenar os dados
    $data = array();

    // Loop para percorrer os resultados da consulta
    while ($row = $result->fetch_assoc()) {
        // Concatenar data e hora
        $dataHora = $row['data'] . ' ' . $row['hora'];
        // Formatar a data para o formato desejado (DD/MM/YYYY)
        $dataFormatada = date('d/m/Y', strtotime($row['data']));
        // Adicionar os dados ao array
        $data[] = array(
            $dataHora, // Manter a data e hora como string
            (float)$row['taxa_mesa'],
            (float)$row['fonte'],
            (float)$row['cofre'],
            (float)$row['ar'],
            (float)$row['resina']
        );
    }

    // Fechar a conexão com o banco de dados
    $conn->close();

    // Retornar os dados obtidos
    return $data;
}

// Definindo os índices de início e fim para a consulta
$inicio = 0;
$fim = 9;

// Verificar se uma data foi selecionada
if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
    $selectedStartDate = DateTime::createFromFormat('d/m/Y', $_GET['startDate'])->format('Y-m-d');
    $selectedEndDate = DateTime::createFromFormat('d/m/Y', $_GET['endDate'])->format('Y-m-d');
    $dadosFiltrados = obterDadosDoReatorFiltrados($inicio, $fim, $selectedStartDate, $selectedEndDate);
} else {
    $dadosFiltrados = obterDadosDoReatorFiltrados($inicio, $fim);
}

// Obter a data mais recente dos dados filtrados
$latestDateFormatted = !empty($dadosFiltrados) ? date('d/m/Y', strtotime($dadosFiltrados[0][0])) : null;

// Obter a data e hora atual em São Paulo
$currentDateTime = new DateTime("now", new DateTimeZone('America/Sao_Paulo'));
$currentDateTimeFormatted = $currentDateTime->format('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gráfico</title>
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
    <!-- Adicionando jQuery para manipulação de eventos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <!-- Adicionando jQuery UI para criar os calendários -->
</head>

<body>
    <div id="curve_chart" style="width: 900px; height: 500px"></div>
    <br>
    <!-- Formulário para selecionar a data -->
    <form id="dateForm">
        <input type="text" id="datepicker1">
        <input type="text" id="datepicker2">
        <button type="button" onclick="submitForm()">Filtrar por Data</button>
    </form>

    <!-- Botão para redirecionar para outra página -->
    <!--<button onclick="redirectToTab3()">Ir para Tabela</button>-->

    <script type='text/javascript'>
        google.charts.load('current', {
            'packages': ['annotatedtimeline']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var dadosFiltrados = <?php echo json_encode($dadosFiltrados); ?>;
            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Data e Hora'); // Alterado para 'datetime'
            data.addColumn('number', 'Taxa Mesa');
            data.addColumn('number', 'Fonte');
            data.addColumn('number', 'Cofre');
            data.addColumn('number', 'AR');
            data.addColumn('number', 'Resina');
            for (let i = 0; i < dadosFiltrados.length; i++) {
                // Criar objeto Date diretamente a partir da string de data
                let date = new Date(dadosFiltrados[i][0]);
                data.addRow([date, dadosFiltrados[i][1], dadosFiltrados[i][2], dadosFiltrados[i][3], dadosFiltrados[i][4], dadosFiltrados[i][5]]);
            }
            var options = {
                title: 'Dados do Reator',
                curveType: 'function',
                legend: {
                    position: 'bottom'
                },
                vAxis: {
                    title: 'Valores'
                }
            };

            var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('curve_chart'));
            chart.draw(data, options);
        }

        // Função para redirecionar para tab3.php com os parâmetros do período selecionado
        /*function redirectToTab3() {
            var selectedStartDate = $("#datepicker1").val();
            var selectedEndDate = $("#datepicker2").val();
            if (selectedStartDate === "" || selectedEndDate === "") {
                alert("Favor selecionar ambas as datas");
                return;
            }
            window.location.href = 'tab3.php?startDate=' + selectedStartDate + '&endDate=' + selectedEndDate;
        }*/

        
        $(function() {
            var urlParams = new URLSearchParams(window.location.search);
            var selectedStartDate = urlParams.get('startDate') || '<?php echo $latestDateFormatted; ?>';
            var selectedEndDate = urlParams.get('endDate') || '<?php echo $latestDateFormatted; ?>';

            $("#datepicker1").datepicker({
                dateFormat: 'dd/mm/yy'
            });

            // Define a data inicial do gráfico como valor padrão do primeiro calendário
            $("#datepicker1").datepicker('setDate', selectedStartDate);

            $("#datepicker2").datepicker({
                dateFormat: 'dd/mm/yy'
            });

            // Define a data final do gráfico como valor padrão do segundo calendário
            $("#datepicker2").datepicker('setDate', selectedEndDate);
        });

        function submitForm() {
            var selectedStartDate = $("#datepicker1").val();
            var selectedEndDate = $("#datepicker2").val();
            if (selectedStartDate === "" || selectedEndDate === "") {
                alert("Favor selecionar ambas as datas");
                return;
            }
            // Redirecionar para a mesma página com as datas selecionadas como parâmetros
            window.location.href = '?startDate=' + selectedStartDate + '&endDate=' + selectedEndDate;
        }
    </script>
</body>

</html>
