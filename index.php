<?php 
// Verifica se os dados foram mandados via POST
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = (isset($_POST["id"]) && $_POST["id"] != null) ? $_POST["id"]: "";
    $nome =(isset($_POST["nome"]) && $_POST["nome"] != null) ? $_POST["nome"]: "";
    $email =(isset($_POST["email"]) && $_POST["email"] != null) ? $_POST["email"]: "";
    $cell =(isset($_POST["celular"]) && $_POST["celular"] != null) ? $_POST["celular"]: "";
} else if(!isset($id)) {
    $id = (isset($_GET['id']) && $_GET['id'] != null) ? $_GET['id'] : '';
    $nome = null;
    $email = null;
    $cell = null;
}


?>

<?php

// Dados de login com o banco de dados

$dsn = 'mysql:host=127.0.0.1:3306;dbname=tstcrud;';
$user = "root";
$password = "";

// Tentativa de ligação com o db(database ou banco de dados)

try{
    $conexao = new PDO($dsn, $user, $password);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->exec("set names utf8");

//Cadastro dos Dados no db (SEÇÃO CREATE e UPDATE)
    if(isset($_REQUEST["act"]) && $_REQUEST["act"] == "save" && $nome != ""){
        try {
            $stmt = $conexao->prepare("INSERT INTO contact (nome, email, numero) VALUES (?, ?, ?)"); //Declaração de Objeto que pode ser manipulado pelo comando bindParam.
            $stmt->bindParam(1, $nome); //Ocupa a primeira Posição da Declaração de Objeto já com a variavel inclusa.
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $cell);

            if($stmt->execute()){ //Um if para tratamento de erros para verificar se a comunicação com o db ocorreu bem.
                if($stmt->rowCount() > 0){ //Mais um if que retorna o numero de linhas afetadas, e se foi mais de 0, significa que deu certo o cadastro
                    echo "Dados cadastrados :)";
                    $id = null;
                    $nome = null;
                    $email = null;
                    $cell = null; //Limpa-se as variaveis para que o Usuario não se cadastre novamente.
                    if($id != ""){
                        $stmt = $conexao->prepare("UPDATE contact SET nome=?, email=?, numero=? WHERE id = ?");
                        $stmt->bindParam(4, $id);
                    } else{
                        $stmt = $conexao->prepare("INSERT INTO contact (nome, email, numero) VALUES (?, ?, ?)");
                    }
                } else{
                    echo "Erro ao cadastrar :(";
                }

            } else{
                throw new PDOException("Erro: Não foi possivel executar a declaração SQL");
            }

            //SEÇÃO UPDATE
            if(isset($_REQUEST["act"]) && $_REQUEST["act"] == "upd" && $id != ""){
                try{
                    $stmt = $conexao->prepare("SELECT * FROM contact WHERE id = ?");
                    $stmt->bindParam(1, $id, PDO::PARAM_INT);
                    if($stmt->execute()){
                        $rs = $stmt->fecth(PDO::FETCH_OBJ);
                        $id = $rs->id;
                        $nome = $rs->nome;
                        $email = $rs->email;
                        $cell = $rs->numero;
                    } else{
                        throw new PDOException("Erro: Não foi possivel executar ação");
                    }
                }catch(PDOException $erro){
                    echo "Erro: ".$erro->getMessage();
                }
            }

        } catch (PDOException $erro){
            echo "ERR: " . $erro -> getMessage(); //Tratamento de Erros
        }
    }
        //Seção DELETE
        if(isset($_REQUEST["act"]) && $_REQUEST["act"] == "del" && $id != ""){
            try{
                $stmt = $conexao->prepare("DELETE FROM contact WHERE id = ?");
                $stmt->bindParam(1, $id, PDO::PARAM_INT);
                if($stmt->execute()){
                    echo "Registro Apagado com Sucesso!";
                    $id = null;
                }else{
                    throw new PDOException("ERR: Não foi possivel Apagar o registro.");
                }

            }catch(PDOException $erro){
                echo "ERR: ".$erro->getMessage();
            }
        }

    

//Mostragem de Erro se não se conectar com o db
}catch (PDOException $erro){
    echo "Erro na conexão:" . $erro->getMessage(); //Tratamento de Erros
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Agenda de contatos</title>
    </head>
    <body>
        <form action="?act=save" method="POST" name="form1" >
          <h1>Agenda de contatos</h1>
          <hr>

          <!-- Esses if's são verificadores para ver se não está vazio. -->

          <input type="hidden" name="id" /> <?php
          if(isset($id) && $id != null || $id != ""){
            echo "value=\"{$id}\"";
          }
           ?>

          Nome:
          <input type="text" name="nome" /> <?php
          if(isset($nome) && $nome != null || $nome != ""){
            echo "value=\"{$nome}\"";
          }
           ?>

          E-mail:
          <input type="email" name="email" /><?php
          if(isset($email) && $email != null || $email != ""){
            echo "value=\"{$email}\"";
          }
           ?>

          Celular:
         <input type="number" name="celular" /> <?php
          if(isset($cell) && $cell != null || $cell != ""){
            echo "value=\"{$cell}\"";
          }
           ?>

         <input type="submit" value="salvar" />
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
    <?php //SEÇÃO READ
    try {
        $stmt = $conexao->prepare("SELECT * FROM contact");
            if($stmt->execute()) {
                while ($rs = $stmt->fetch(PDO::FETCH_OBJ)){ //O $rs (result set) busca a informação em forma de objeto
                    echo "<tr>";
                    echo "<td>".$rs->nome."</td><td>".$rs->email."</td><td>".$rs->numero."</td><td><center><a href=\"?act=upd&id=" . $rs->id . "\">[Alterar]</a>"."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<a href=\"?act=del&id=" . $rs->id . "\">[Excluir]</a></center></td>"; //Adiciona as Infos na tabela
                    echo "</tr>";
                }
            }   else {
                echo "ERR:Não foi possível resgatar os dados do Servidor.";
            }
    } catch(PDOException $erro){
        echo "ERR: ". $erro->getMessage(); //Tratamento de Erros
    }
    ?>
</table>

    </body>