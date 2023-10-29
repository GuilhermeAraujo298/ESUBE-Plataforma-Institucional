<?php 
    $ra_id = $_POST["RA_ID"];
    $password = $_POST["password"];

    if(!filter_var($ra_id,FILTER_VALIDATE_INT)){
        header("Location: ../Login/pagLogin.php?invalidLogin");
        exit();
    }
    if($password == ""){
        header("Location: ../Login/pagLogin.php?invalidLogin");
        exit();
    }

    //Validar com o banco
    include_once "conexao.php";
    $db = new Conexao();
    if($db->errorCode != 0){
        //Houve um erro de conexão
        header("Location: ../Login/pagLogin.php?ERROR=1");
        exit();
    }

    //Buscar usuário
    //$result = $db->executar("SELECT u.id from usuarios as u LEFT JOIN funcionarios as f ON u.id = f.id WHERE f.id IS null;");
    $result = $db->executar("SELECT ra from usuarios;");
    $userValid = false;
    foreach($result as $c){
        //Valida usuário
        if($c[0] == $ra_id){
            $userValid = true;
            break;
        }
    }
    if(!$userValid){
        header("Location: ../Login/pagLogin.php?invalidLogin");
        exit();
    }

    //Valida senha
    $result = $db->executar("SELECT senha FROM usuarios WHERE ra = $ra_id;");
    if(!password_verify($password, $result[0]['senha']) && $result[0][0] != $password){ // IMPORTANTE -> A segunda parte do '&&' (E) deve ser removida após a padronização da criptografia!
        header("Location: ../Login/pagLogin.php?invalidLogin=".$result[0]['senha']);
        exit();
    }


    //Concluir login na sessão e Indentificar tipo de usuário
    include_once "sessao.php";
    $_SESSION[SESSION_USER_RA_ID] = $ra_id;

    $result = $db->executar("SELECT nome FROM usuarios WHERE ra = $ra_id;");
    $_SESSION[SESSION_USERNAME] = $result[0][0];

    $result = $db->executar("SELECT tipo FROM usuarios WHERE ra = $ra_id",true);
    $permisson = 0;
    if($result->rowCount() == 3){
        $permisson = PERMISSION_ALUNO;
    }else{
        $result = $result->fetchAll();
        $permisson = $result[0][0];
    }
    $_SESSION[SESSION_USER_IDPERMISSION] = $permisson;

    
    //Redirecionar
    redirectByPermission($permisson);
?>