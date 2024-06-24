# Grafico-Instituto-de-Engenharia-Nuclear
Esse projeto se trata de um gráfico que fizemos para o IEN, com o intuito de auxiliar no estudo do comportamento do reator nuclear.

Introdução: Esse código se trata de uma ferramenta que busca informações no banco de dados MariaDB do reator e as expressa em um gráfico. Ao ser aberto inicialmente, apresenta apenas as informações do último dia com registros no banco de dados, podendo o usuário fazer a seleção de data inicial e final para a exibição das informações desejadas. O gráfico contém uma timeline que permite selecionar o período de tempo a ser exibido, aproximando ou afastando automaticamente os dados como for do interesse do usuário. Para a construção do gráfico foi utilizado o PHP para Backend, fazendo a conexão e buscando informações no o banco de dados MariaDB, Google Charts onde foi obtido o modelo do gráfico e jQuery UI para a confecção do calendário e seleção das datas.

1. PHP Backend (obterDadosDoReatorFiltrados):

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
?>

    Propósito: Esta função PHP conecta ao banco de dados MariaDB para obter dados do reator.
    Parâmetros:
        $inicio, $fim: Índices de início e fim para a consulta (atualmente não utilizados internamente).
        $selectedStartDate, $selectedEndDate: Datas de início e fim para filtrar os dados.
    Funcionalidade:
        Verifica se datas de início e fim foram fornecidas; se sim, executa uma consulta SQL para obter dados dentro desse intervalo.
        Caso contrário, obtém a data mais recente do banco de dados e consulta os dados correspondentes.
        Formata os dados obtidos e os retorna como um array estruturado para serem usados no frontend.

2. Frontend HTML/JavaScript (Google Charts e jQuery UI):

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gráfico</title>
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</head>

<body>
    <div id="curve_chart" style="width: 900px; height: 500px"></div>
    <br>
    <form id="dateForm">
        <input type="text" id="datepicker1">
        <input type="text" id="datepicker2">
        <button type="button" onclick="submitForm()">Filtrar por Data</button>
    </form>

    <script type='text/javascript'>
        google.charts.load('current', {
            'packages': ['annotatedtimeline']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var dadosFiltrados = <?php echo json_encode($dadosFiltrados); ?>;
            var data = new google.visualization.DataTable();
            data.addColumn('datetime', 'Data e Hora');
            data.addColumn('number', 'Taxa Mesa');
            data.addColumn('number', 'Fonte');
            data.addColumn('number', 'Cofre');
            data.addColumn('number', 'AR');
            data.addColumn('number', 'Resina');
            for (let i = 0; i < dadosFiltrados.length; i++) {
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

        $(function() {
            var urlParams = new URLSearchParams(window.location.search);
            var selectedStartDate = urlParams.get('startDate') || '<?php echo $latestDateFormatted; ?>';
            var selectedEndDate = urlParams.get('endDate') || '<?php echo $latestDateFormatted; ?>';

            $("#datepicker1").datepicker({
                dateFormat: 'dd/mm/yy'
            });
            $("#datepicker1").datepicker('setDate', selectedStartDate);

            $("#datepicker2").datepicker({
                dateFormat: 'dd/mm/yy'
            });
            $("#datepicker2").datepicker('setDate', selectedEndDate);
        });

        function submitForm() {
            var selectedStartDate = $("#datepicker1").val();
            var selectedEndDate = $("#datepicker2").val();
            if (selectedStartDate === "" || selectedEndDate === "") {
                alert("Favor selecionar ambas as datas");
                return;
            }
            window.location.href = '?startDate=' + selectedStartDate + '&endDate=' + selectedEndDate;
        }
    </script>
</body>

</html>

    Propósito: Esta parte do código HTML/JavaScript define a interface do usuário e implementa o gráfico de linha usando Google Charts.
    Funcionalidade:
        HTML:
            Inclui os scripts necessários do Google Charts e jQuery UI.
            Define um formulário com dois campos de data (#datepicker1 e #datepicker2) e um botão para filtrar os dados.
        JavaScript:
            Google Charts:
                Carrega e desenha o gráfico de linha (AnnotatedTimeLine) com os dados obtidos do PHP.
	Link do AnnotatedTimeLine:
https://developers.google.com/chart/interactive/docs/gallery/annotatedtimeline?hl=pt-br
	
            jQuery UI:
                Inicializa os campos de data como calendários usando datepicker.
                Recupera e define as datas de início e fim do URL da consulta (se existirem) ou usa a data mais recente obtida do PHP.
            Funções:
                submitForm(): Redireciona para a mesma página com os parâmetros de datas selecionadas como parâmetros de URL ao clicar no botão "Filtrar por Data".

Funcionamento Geral

    Backend (obterDadosDoReatorFiltrados):
        Conecta ao banco de dados para buscar dados do reator.
        Filtra os dados com base nas datas selecionadas ou retorna os dados mais recentes.
        Formata os dados para serem compatíveis com o formato de entrada do Google Charts.

    Frontend:
        Interface do Usuário: Permite ao usuário selecionar um intervalo de datas para filtrar os dados exibidos no gráfico.
        Google Charts: Utiliza os dados formatados pelo PHP para exibir um gráfico de linha interativo com valores de diferentes variáveis do reator ao longo do tempo.
        jQuery UI: Facilita a seleção de datas através de calendários interativos.

Este código exemplifica a integração entre PHP para manipulação de dados do servidor e JavaScript para interação do usuário e visualização de dados dinâmicos através de gráficos.
