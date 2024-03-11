<?php
// Incluindo o arquivo de conexão com o banco de dados
require 'teste.php';

// Verifica se a requisição é do tipo GET e se o parâmetro 'arquivo' está definido na URL
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['arquivo'])) {
    // Conecta ao banco de dados
    $conn = conectarBancoDados();

    // Obtém o nome do arquivo da URL
    $nomeArquivo = $_GET['arquivo'];

    // Consulta SQL para obter o conteúdo do arquivo
    $sql = "SELECT conteudo_arquivo FROM arquivos WHERE nome_arquivo = '$nomeArquivo'";
    $resultado = $conn->query($sql);

    // Verifica se a consulta retornou algum resultado
    if ($resultado->num_rows > 0) {
        // Obtém o conteúdo do arquivo
        $row = $resultado->fetch_assoc();
        $conteudoArquivo = $row['conteudo_arquivo'];

        // Configurações do cabeçalho HTTP para forçar o download
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$nomeArquivo");

        // Envia o conteúdo do arquivo como resposta HTTP
        echo $conteudoArquivo;
    } else {
        // Se o arquivo não foi encontrado no banco de dados
        echo "Arquivo não encontrado.";
    }

    // Fecha a conexão com o banco de dados
    $conn->close();
} else {
    // Se a requisição não for do tipo GET ou se o parâmetro 'arquivo' não estiver definido na URL
    echo "Acesso não autorizado.";
}
?>