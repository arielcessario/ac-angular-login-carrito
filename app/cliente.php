<?php
/**
 * Created by PhpStorm.
 * Cliente: kn
 * Date: 16/03/15
 * Time: 19:13
 */

session_start();
require_once 'MyDBi.php';

$data = file_get_contents("php://input");

$decoded = json_decode($data);

if ($decoded->function == 'login') {
    login($decoded->mail, $decoded->password);
} else if ($decoded->function == 'checkLastLogin') {
    checkLastLogin($decoded->userid);
} else if ($decoded->function == 'create') {
    create($decoded->user);
} else if ($decoded->function == 'getClienteByEmail') {
    getClienteByEmail($decoded->email);
} else if ($decoded->function == 'resetPassword') {
    resetPassword($decoded->user, $decoded->new_password, $decoded->changepwd);
} else if ($decoded->function == 'getClienteByEmailAndPassword') {
    getClienteByEmailAndPassword($decoded->email, $decoded->password);
} else if ($decoded->function == 'existeCliente') {
    existeCliente($decoded->username);
}

function login($mail, $password)
{
    $db = new MysqliDb();
    $db->where("mail", $mail);

    $results = $db->get("clientes");

    $hash = $results[0]['password'];

    if (password_verify($password, $hash)) {        
        $userId = $results[0]['cliente_id'];
        echo json_encode($userId);

//        $token = password_hash(rand(), PASSWORD_BCRYPT);
//        $token = str_replace('/','',$token);
//        $data = array('last_login' => $db->now(),
//            'token' => $token);
//        $db->where('cliente_id', $userId);
//
//        $results[0]['token'] = $token;
//
//        if ($db->update('clientes', $data)) {
//            $response = ['response' => true, 'user' => json_encode($results[0]), 'pwd' => $password, 'hash' => $hash, 'pwd_info' => password_get_info($hash)];
//        } else {
//            $response = ['response' => false, 'pwd' => $password, 'hash' => $hash, 'pwd_info' => password_get_info($hash)];
//        }
//
//        echo json_encode($response);
    } 
    else {

        echo json_encode(-1);
//        $response = ['response' => false, 'pwd' => $password, 'hash' => $hash, 'pwd_info' => password_get_info($hash)];
//        echo json_encode($response);
    }
}

function checkLastLogin($userid)
{
    $db = new MysqliDb();
    $results = $db->rawQuery('select TIME_TO_SEC(TIMEDIFF(now(), last_login)) diferencia from clientes where cliente_id = ' . $userid);

    if ($db->count < 1) {
        $db->rawQuery('update clientes set token ="" where cliente_id =' . $userid);
        echo(json_encode(false));
    }
    else {
        $diff = $results[0]["diferencia"];

        if (intval($diff) < 12960) {
            echo(json_encode($results[0]));
        } else {
            $db->rawQuery('update clientes set token ="" where cliente_id =' . $userid);
            echo(json_encode(false));
        }
    }
}


function create($user)
{
    $db = new MysqliDb();
    $user_decoded = json_decode($user);
    $options = ['cost' => 12];
    $password = password_hash($user_decoded->password, PASSWORD_BCRYPT, $options);

    $data = array(
        'nombre' => $user_decoded->nombre,
        'apellido' => $user_decoded->apellido,
        'mail' => $user_decoded->mail,
        'password' => $password);

    $result = $db->insert('clientes', $data);
    if ($result > -1) {
        echo json_encode(true);
    } else {
        echo json_encode(false);
    }
}

/**
 * esta funcion me retorna un cliente filtrando x email
 * @param $email
 */
function getClienteByEmail($email)
{
    //Instancio la conexion con la DB
    $db = new MysqliDb();
    //Armo el filtro por email
    $db->where("email", $email);
    //Que me retorne el cliente filtrando por email
    $results = $db->get("clientes");

    //Serializo el resultado
    $response = ['user' => json_encode($results[0])];

    //retorno el resultado serializado
    echo json_encode($response);
}

function resetPassword($user, $new_password, $changepwd)
{
    $db = new MysqliDb();
    $user_decoded = json_decode($user);
    $options = ['cost' => 12];
    $password = password_hash($new_password, PASSWORD_BCRYPT, $options);

    $data = array('password' => $password,
        'changepwd' => $changepwd);

    $db->where('cliente_id', $user_decoded->cliente_id);
    if ($db->update('clientes', $data)) {
        echo json_encode(['result' => true, 'new_password' => $new_password, 'password_hashed' => $password, 'pwd_info' => password_get_info($password)]);
    } else {
        echo json_encode(['result' => false, 'new_password' => $new_password, 'password_hashed' => $password, 'pwd_info' => password_get_info($password)]);
    }
}

function getClienteByEmailAndPassword($email, $password)
{
    //Instancio la conexion con la DB
    $db = new MysqliDb();
    //Armo el filtro por email
    $db->where("email", $email);
    //Que me retorne el cliente filtrando por email
    $results = $db->get("clientes");

    $hash = $results[0]['password'];

    if (password_verify($password, $hash)) {
        $response = ['user' => json_encode($results[0]), 'result' => true, 'password' => $password, 'hash' => $hash, 'pwd_info' => password_get_info($hash)];
    }
    else {
        $response = ['user' => json_encode(null), 'result' => false, 'password' => $password, 'hash' => $hash, 'pwd_info' => password_get_info($hash)];
    }
    //retorno el resultado serializado
    echo json_encode($response);
}

function existeCliente($username)
{
     //Instancio la conexion con la DB
    $db = new MysqliDb();
    //Armo el filtro por email
    $db->where("user_name", $username);
    //Que me retorne el cliente filtrando por email
    $results = $db->get("clientes");

    //Serializo el resultado
//    $response = ['user' => json_encode($results[0])];

    //retorno el resultado serializado
    if($db->count > 0){

        echo json_encode(true);
    }else{
        echo json_encode(false);

    }
}