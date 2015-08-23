<?php
/**
 * Created by PhpStorm.
 * Cliente: kn
 * Date: 16/03/15
 * Time: 19:13
 */

session_start();
if (file_exists('../../../MyDBi.php')) {
    require_once '../../../MyDBi.php';
} else {
    require_once 'MyDBi.php';
}


$data = file_get_contents("php://input");

$decoded = json_decode($data);

if($decoded != null) {
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
    } else if ($decoded->function == 'changePassword') {
        changePassword($decoded->cliente_id, $decoded->pass_old, $decoded->pass_new);
    } else if ($decoded->function == 'getHistoricoPedidos') {
        getHistoricoPedidos($decoded->cliente_id);
    } else if ($decoded->function == 'update') {
        update($decoded->user);
    }
}
else{
    $function = $_GET["function"];
    if ($function == 'getHistoricoPedidos') {
        getHistoricoPedidos($_GET["cliente_id"]);
    }elseif ($function == 'getClientes') {
        getClientes();
    }
}


function getClientes(){
    $db = new MysqliDb();
    $results = $db->get('clientes');
    echo json_encode($results);
}


function login($mail, $password)
{
    $db = new MysqliDb();
    $db->where("mail", $mail);

    $results = $db->get("clientes");

    if ($db->count > 0) {

        $hash = $results[0]['password'];
        if (password_verify($password, $hash)) {
            echo json_encode($results);
        } else {

            echo json_encode(-1);
        }
    } else {
        echo json_encode(-1);
    }


}

function checkLastLogin($userid)
{
    $db = new MysqliDb();
    $results = $db->rawQuery('select TIME_TO_SEC(TIMEDIFF(now(), last_login)) diferencia from clientes where cliente_id = ' . $userid);

    if ($db->count < 1) {
        $db->rawQuery('update clientes set token ="" where cliente_id =' . $userid);
        echo(json_encode(false));
    } else {
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

//    $nro_doc = '';
    if (array_key_exists('nro_doc', $user_decoded)) {
        $nro_doc = $user_decoded->nro_doc;
    }else{
        $nro_doc = '';
    }


    $data = array(
        'nombre' => $user_decoded->nombre,
        'apellido' => $user_decoded->apellido,
        'mail' => $user_decoded->mail,
        'password' => $password,
        'nro_doc' => $nro_doc,
        'fecha_nacimiento' => $user_decoded->fecha_nacimiento,
        'direccion' => $user_decoded->direccion,
        'telefono' => $user_decoded->telefono
    );

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
    } else {
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
    $db->where("mail", $username);

    //Que me retorne el cliente filtrando por email
    $results = $db->get("clientes");

    //Serializo el resultado
//    $response = ['user' => json_encode($results[0])];

    //retorno el resultado serializado
    if ($db->count > 0) {

        echo json_encode(true);
    } else {
        echo json_encode(false);

    }
}

function changePassword($cliente_id, $pass_old, $pass_new)
{
    $db = new MysqliDb();

    $db->where('cliente_id', $cliente_id);
    $results = $db->get("clientes");

    if ($db->count > 0) {
        $result = $results[0];

        if (password_verify($pass_old, $result['password'])) {

            $options = ['cost' => 12];
            $password = password_hash($pass_new, PASSWORD_BCRYPT, $options);

            $data = array('password' => $password);
            if ($db->update('clientes', $data)) {
                echo json_encode(1);
            } else {
                echo json_encode(-1);
            }
        }
    } else {
        echo json_encode(-1);
    }
}

function update($user)
{
    $db = new MysqliDb();
    $user_decoded = json_decode($user);

    $db->where('cliente_id', $user_decoded->cliente_id);

    if (array_key_exists('nro_doc', $user_decoded)) {
        $nro_doc = $user_decoded->nro_doc;
    }else{
        $nro_doc = '';
    }

    $data = array(
        'nombre' => $user_decoded->nombre,
        'apellido' => $user_decoded->apellido,
        'mail' => $user_decoded->mail,
        'nro_doc' => $nro_doc,
        'direccion' => $user_decoded->direccion
    );

    if ($db->update('clientes', $data)) {
        echo json_encode(['result' => true]);
    } else {
        echo json_encode(['result' => false]);
    }
}

function getHistoricoPedidos($cliente_id)
{
    $db = new MysqliDb();

    $pedidos = array();

    $SQL = "SELECT carritos.carrito_id,
            carritos.status,
            carritos.total,
            date(carritos.fecha) as fecha,
            carritos.cliente_id,
            0 detalles
            FROM carritos
            WHERE cliente_id = " . $cliente_id . " ORDER BY carritos.carrito_id DESC;";

    $results = $db->rawQuery($SQL);

    foreach ($results as $row) {
        $SQL = 'SELECT
                carrito_detalle_id,
                p.producto_id,
                cantidad,
                precio,
                p.nombre
                FROM carrito_detalles cd
                INNER JOIN productos p
                ON cd.producto_id = p.producto_id
                WHERE carrito_id = ' . $row['carrito_id'] . ';';

        $detalle = $db->rawQuery($SQL);
        $row['detalles'] = $detalle;
        array_push($pedidos, $row);
    }

    echo json_encode($pedidos);
}