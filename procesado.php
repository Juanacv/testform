<?php
include 'vars.php';
function getHtmlFromUrl($url) {
    $ch = curl_init($url);
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false); // No incluir las cabeceras en la respuesta

    // Ejecutar la solicitud y obtener el contenido de la URL
    $response = curl_exec($ch);

    // Verificar si hay errores en la solicitud
    if ($response === false) {
        die('Error cURL: ' . curl_error($ch));
    }
    // Cerrar la sesión cURL
    curl_close($ch);
    // Crear el iframe con el contenido obtenido
    echo '<iframe srcdoc="' . htmlspecialchars($response) . '" width="500px" height="500px"></iframe>';

}
function printArray($values) {
    foreach($values as $key => $value) {
        if (strpos($value,".jpg") !== false || strpos($value,".png") !== false)  {
            echo "<img src=\"uploads/$value\" alt=\"retrato\" width=\"400\" height=\"400\">";
        }
        else if (strpos($value,"http") !== false) {
            getHtmlFromUrl($value);
        }
        else {
            echo $value.'<br>';
        }
    }
}
function filtering($input) {
    $input = trim($input); // Elimina espacios antes y después de los datos
    $input = stripslashes($input); // Elimina backslashes \
    $input = htmlspecialchars($input); // Traduce caracteres especiales en entidades HTML
    return $input;
}
function movePortrait($arrayFile, $tmpDir) {
    $uploadDir ='uploads/'; 
    // Comprobamos y renombramos el nombre del archivo
    $fileName = $arrayFile['filename'];
    $extension = $arrayFile['extension'];
    $fileName = preg_replace("/[^A-Z0-9._-]/i", "_", $fileName);
    $fileName = $fileName . rand(1, 100);
    // Desplazamos el archivo si no hay errores
    $fullName = $uploadDir.$fileName.".".$extension;
    move_uploaded_file($tmpDir, $fullName);
    return $fileName.".".$extension;
}
function checkPortrait($fileName, $fileSize, $tmpDir) {
    $max_file_size = "1572864";
    $validExtensions = array("jpg", "jpeg", "png");    
    $arrayFile = pathinfo($fileName);
    $extension = $arrayFile['extension'];
    // Comprobamos la extensión del archivo
    if(!in_array($extension, $validExtensions)){
        return false;
    }
    // Comprobamos el tamaño del archivo
    if($fileSize > $max_file_size){
        return false;
    }
    return movePortrait($arrayFile, $tmpDir);
}
function uploadFile() {
    if (!empty($_FILES['retrato']['name'])) {
        $fileName = $_FILES['retrato']['name'];
        $fileSize = $_FILES['retrato']['size'];
        $tmpDir = $_FILES['retrato']['tmp_name'];
        return checkPortrait($fileName, $fileSize, $tmpDir);
    } else {
        return false;
    }  
}
function checkErrors($validations, $erroMessages) {
    $errors = array();
    $valuesForInsert = array();
    foreach($validations as $name => $value)  {
        if ($value === false) {
            array_push($errors,$erroMessages[$name]);
        }
        array_push($valuesForInsert,$value);
    }
    return  [$errors,$valuesForInsert];
}
function extractTotal($result) {
    $total = 0;
    if ($result) {
        $row = $result->fetch_assoc();
        // Acceso al valor utilizando el alias "total"
        $total = $row['total'];
        $result->free_result(); // Liberar los resultados
    }
    return $total;
}
function insertValues($valuesForInsert) {    
    // Conexión a la base de datos
    $connection = new mysqli("localhost", "root", "root", "test");

    // Verificar la conexión
    if ($connection->connect_error) {
        die("Error de conexión: " . $connection->connect_error);
    }

    // Consulta preparada
    $sql = "INSERT INTO datos (nombre, apellidos, edad, email, url, educacion, nacionalidad, idiomas, retrato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);

    // Vincular parámetros
    //$stmt->bind_param('ssissssss', $validations['nombre'], $validations['apellidos'], $validations['edad'],$validations['email'],$validations['url'], $validations['educacion'],$validations['nacionalidad'],$validations['idiomas'],$validations['retrato']);

    // Ejecutar la consulta
    if ($stmt->execute($valuesForInsert)) {
        $lastId = $connection->insert_id;
        $stmt->close();
        $query = "SELECT count(*) as total FROM datos";
        $result = $connection->query($query);
        $total = extractTotal($result);        
        echo "Inserción exitosa id: ".$lastId." de ".$total.'<br>';
    } else {
        echo "Error en la inserción: " . $stmt->error;
    }
    $connection->close();
}
function insertAndPrint($values) {
    insertValues($values);
    printArray($values);
}


$validations = [];

if (isset($_POST['enviar']) &&  $_SERVER['REQUEST_METHOD']=='POST') {
    $validations['nombre'] = !empty($_POST["nombre"])? filtering($_POST["nombre"])  : false;
    $validations['apellidos'] = !empty($_POST["apellidos"])? filtering($_POST["apellidos"])  : false;
    $validations['edad'] = filter_var(intval(filtering($_POST['edad'])),FILTER_VALIDATE_INT, $optionsAge);
    $validations['email'] = filter_var(filtering($_POST['email']), FILTER_VALIDATE_EMAIL);
    $validations['url'] = filter_var(filtering($_POST['url']), FILTER_VALIDATE_URL);
    $validations['url'] = (strlen($validations['url']) <= 255) ? $validations['url'] : false;
    $validations['educacion'] = !empty($_POST["educacion"])? filtering($_POST["educacion"])  : false;
    $validations['nacionalidad'] = !empty($_POST["nacionalidad"])? filtering($_POST["nacionalidad"])  : false;
    // Utilizamos implode para pasar el array a string
    $validations['idiomas'] = !empty($_POST["idiomas"]) ? filtering(implode(", ", $_POST["idiomas"])) : false;    
    $validations['retrato'] = uploadFile();
    
    list($errors, $values) = checkErrors($validations, $erroMessages);
    empty($errors) ?  insertAndPrint($values) : printArray($errors);
}
else {
    echo "No han llegado datos";
}

