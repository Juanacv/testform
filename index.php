<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeScript: Hello, World!</title>
</head>
<body>
    <form action="procesado.php" method="POST" enctype="multipart/form-data">
        Nombre: <input type="text" name="nombre"><br>
        Apellidos: <input type="text" name="apellidos"><br>
        Edad: <input type="number" name="edad" id=""><br>
        Email: <input type="email" name="email" id=""><br>
        Url (que empiece por http): <input type="text" name="url" id=""><br>
        Educacion:
        <select name="educacion">
            <option value="sin-estudios">Sin estudios</option>
            <option value="educacion-obligatoria" selected="selected">Educación Obligatoria</option>
            <option value="formacion-profesional">Formación profesional</option>
            <option value="universidad">Universidad</option>
        </select> <br>
        Nacionalidad:
        <input type="radio" name="nacionalidad" value="española">Española</input>
        <input type="radio" name="nacionalidad" value="otra">Otra</input><br>
        Idiomas:
        <input type="checkbox" name="idiomas[]" value="español" checked="checked">Español</input>
        <input type="checkbox" name="idiomas[]" value="inglés">Inglés</input>
        <input type="checkbox" name="idiomas[]" value="francés">Francés</input>
        <input type="checkbox" name="idiomas[]" value="aleman">Alemán</input><br>        
        Retrato: <input type="file" name="retrato"><br>
        <input type="submit" name="enviar" value="Enviar"><br>
    </form>
</body>
</html>