# Grafico-Instituto-de-Engenharia-Nuclear
Esse projeto se trata de um gráfico que fizemos para o IEN, com o intuito de auxiliar no estudo do comportamento do reator nuclear.

Introdução: Esse código se trata de uma ferramenta que busca informações no banco de dados MariaDB do reator e as expressa em um gráfico. Ao ser aberto inicialmente, apresenta apenas as informações do último dia com registros no banco de dados, podendo o usuário fazer a seleção de data inicial e final para a exibição das informações desejadas. O gráfico contém uma timeline que permite selecionar o período de tempo a ser exibido, aproximando ou afastando automaticamente os dados como for do interesse do usuário. Para a construção do gráfico foi utilizado o PHP para Backend, fazendo a conexão e buscando informações no o banco de dados MariaDB, Google Charts onde foi obtido o modelo do gráfico e jQuery UI para a confecção do calendário e seleção das datas.

1. PHP Backend (obterDadosDoReatorFiltrados):

    Propósito: Esta função PHP conecta ao banco de dados MariaDB para obter dados do reator.
    Parâmetros:
        $inicio, $fim: Índices de início e fim para a consulta (atualmente não utilizados internamente).
        $selectedStartDate, $selectedEndDate: Datas de início e fim para filtrar os dados.
    Funcionalidade:
        Verifica se datas de início e fim foram fornecidas; se sim, executa uma consulta SQL para obter dados dentro desse intervalo.
        Caso contrário, obtém a data mais recente do banco de dados e consulta os dados correspondentes.
        Formata os dados obtidos e os retorna como um array estruturado para serem usados no frontend.

2. Frontend HTML/JavaScript (Google Charts e jQuery UI):

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
