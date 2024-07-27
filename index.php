<?php
// Inicializa variáveis
$id = $nome = $email = $cell = '';
$act = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST["id"]) ? $_POST["id"] : "";
    $nome = isset($_POST["nome"]) ? $_POST["nome"] : "";
    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $cell = isset($_POST["celular"]) ? $_POST["celular"] : "";
    $act = isset($_POST["act"]) ? $_POST["act"] : "";
} else if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// Dados de conexão com o banco de dados
$dsn = 'mysql:host=127.0.0.1:3306;dbname=tstcrud;';
$user = "root";
$password = "";

try {
    $conexao = new PDO($dsn, $user, $password);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->exec("set names utf8");

    // Cadastro dos Dados no db (SEÇÃO CREATE e UPDATE)
    if ($act === "save" && $nome != "") {
        try {
            if (!empty($id)) {
                // Atualiza o registro existente
                $stmt = $conexao->prepare("UPDATE contact SET nome=?, email=?, numero=? WHERE id = ?");
                $stmt->bindParam(1, $nome);
                $stmt->bindParam(2, $email);
                $stmt->bindParam(3, $cell);
                $stmt->bindParam(4, $id, PDO::PARAM_INT);

                $stmt->execute();
            } else {
                
                // Insere um novo registro
                $stmt = $conexao->prepare("INSERT INTO contact (nome, email, numero) VALUES (?, ?, ?)");
                $stmt->bindParam(1, $nome);
                $stmt->bindParam(2, $email);
                $stmt->bindParam(3, $cell);
            }

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo "Dados " . ($id ? "atualizados" : "cadastrados") . " :)";
                } else {
                    echo "Nenhuma mudança detectada.";
                }
                // Limpa os campos após a operação
                $id = $nome = $email = $cell = null;
            } else {
                throw new PDOException("Erro: Não foi possível executar a declaração SQL");
            }
        } catch (PDOException $erro) {
            echo "ERR: " . $erro->getMessage();
        }
    }

    // SEÇÃO UPDATE
    if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "upd" && $id != "") {
        try {
            $stmt = $conexao->prepare("SELECT * FROM contact WHERE id = ?");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $rs = $stmt->fetch(PDO::FETCH_OBJ);
                if ($rs) {
                    $nome = $rs->nome;
                    $email = $rs->email;
                    $cell = $rs->numero;
                } else {
                    echo "Registro não encontrado.";
                }
            } else {
                throw new PDOException("Erro: Não foi possível executar a ação");
            }
        } catch (PDOException $erro) {
            echo "Erro: " . $erro->getMessage();
        }
    }

    // Seção DELETE
    if (isset($_REQUEST["act"]) && $_REQUEST["act"] == "del" && $id != "") {
        try {
            $stmt = $conexao->prepare("DELETE FROM contact WHERE id = ?");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                echo "Registro apagado com sucesso!";
                $id = null;
            } else {
                throw new PDOException("ERR: Não foi possível apagar o registro.");
            }
        } catch (PDOException $erro) {
            echo "ERR: " . $erro->getMessage();
        }
    }
} catch (PDOException $erro) {
    echo "Erro na conexão: " . $erro->getMessage();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agenda de contatos</title>
</head>
<body>
    <form action="?" method="POST" name="form1">
        <h1>Agenda de contatos</h1>
        <hr>

        <!-- Campo oculto para o ID -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
        
        Nome:
        <input type="text" name="nome" value="<?php echo htmlspecialchars($nome); ?>" />
        E-mail:
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" />
        Celular:
        <input type="number" name="celular" value="<?php echo htmlspecialchars($cell); ?>" />

        <!-- Campo oculto para ação -->
        <input type="hidden" name="act" value="save" />
        <input type="submit" value="Salvar" />
        <input type="reset" value="Novo" />
        <hr>
    </form>

    <table border="1" width="100%">
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Celular</th>
            <th>Ações</th>
        </tr>
        <?php // SEÇÃO READ
        try {
            $stmt = $conexao->prepare("SELECT * FROM contact");
            if ($stmt->execute()) {
                while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($rs->nome) . "</td><td>" . htmlspecialchars($rs->email) . "</td><td>" . htmlspecialchars($rs->numero) . "</td><td><center><a href=\"?act=upd&id=" . $rs->id . "\">[Alterar]</a>" . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . "<a href=\"?act=del&id=" . $rs->id . "\">[Excluir]</a></center></td>";
                    echo "</tr>";
                }
            } else {
                echo "ERR: Não foi possível resgatar os dados do Servidor.";
            }
        } catch (PDOException $erro) {
            echo "ERR: " . $erro->getMessage();
        }
        ?>
    </table>
</body>
</html>
