<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função para conectar ao banco de dados
function conectarBancoDados() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "pixel";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }
    return $conn;
}

// Configurações do servidor SMTP do Google
$smtpHost = 'smtp.gmail.com';
$smtpPort = 587;
$smtpUser = 'pixelsharequality@gmail.com'; // Seu e-mail do Gmail
$smtpPassword = 'pkiw zuaf ckgq ycmr'; // Sua senha do Gmail
$smtpEncryption = 'tls';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Criar pasta uploads se não existir
    $diretorioArquivos = 'uploads/';
    if (!file_exists($diretorioArquivos)) {
        mkdir($diretorioArquivos, 0777, true);
    }

    // Dados do formulário
    $destinatario = $_POST['email_destinatario'];
    $nomeDestinatario = $_POST['nome_destinatario'];
    $remetente = 'seu_email@exemplo.com'; // Substitua pelo seu e-mail
    $nomeRemetente = 'Seu Nome';

    // Upload do arquivo
    $nomeArquivo = basename($_FILES['fileToUpload']['name']);
    $caminhoArquivo = $diretorioArquivos . $nomeArquivo;
    if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $caminhoArquivo)) {
        // Define o caminho do diretório de upload
        $diretorioArquivos = 'uploads/';

        // Conectar ao banco de dados
        $conn = conectarBancoDados();

        // Lê o conteúdo do arquivo
        $conteudoArquivo = file_get_contents($caminhoArquivo);

        // Prepara o conteúdo do arquivo para ser armazenado no banco de dados
        $conteudoArquivoBanco = $conn->real_escape_string($conteudoArquivo);

        // Insere os detalhes do arquivo no banco de dados
        $sql = "INSERT INTO arquivos (nome_arquivo, conteudo_arquivo, destinatario, remetente) VALUES ('$nomeArquivo', '$conteudoArquivoBanco', '$destinatario', '$remetente')";

        if ($conn->query($sql) === TRUE) {
            // Instanciar a classe PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Configurações do servidor SMTP
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPassword;
                $mail->Port = $smtpPort;
                $mail->SMTPSecure = $smtpEncryption;

                // Configurações do e-mail
                $mail->setFrom($remetente, $nomeRemetente);
                $mail->addAddress($destinatario, $nomeDestinatario);
                $mail->Subject = 'Arquivo Compartilhado via Sistema de Envio';
                $mail->Body = "Olá $nomeDestinatario,\n\nO remetente $nomeRemetente compartilhou um arquivo com você.\n\nClique no link abaixo para fazer o download:\nhttp://localhost/download.php?arquivo=$nomeArquivo"; 
                // Enviar o e-mail
                $mail->send();
                echo 'Arquivo enviado com sucesso!';
            } catch (Exception $e) {
                echo 'Erro ao enviar o e-mail: ', $mail->ErrorInfo;
            }
        } else {
            echo "Erro ao enviar o arquivo: " . $conn->error;
        }

        // Fechar a conexão com o banco de dados
        if ($conn) {
            $conn->close();
        }
    } else {
        echo "Erro ao mover o arquivo para o diretório de uploads.";
    }
}
?>
