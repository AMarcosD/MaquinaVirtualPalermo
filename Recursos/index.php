 <?php
 
 $servername = "localhost";
 $port = "3306";
 $username = "lcars";
 $password = "NCC1701D";
 $dbname = "ingenieria";
 
 
 
 $conn = new MySQLi ($servername, $username, $password, $dbname);
 
 // Error de conexión
 if ($conn->connect_error) {
   die("Error de conexion: " . $conn->connect_error);
 }
 
 $sql = "select alumnos.legajo 'legajo', 
                alumnos.apellido 'apellido', 
                alumnos.nombres 'nombre',
                 modulos.nom_modulo 'materia', 
                notas.nota 'nota'
         from   alumnos, 
                modulos, 
                notas 
         where  alumnos.legajo=notas.legajo 
            and modulos.cod_modulo = notas.cod_modulo;";
 
 if ($result = $conn->query($sql)) {

// Crear una tabla HTML para mostrar los datos
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>Datos de Alumnos</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
echo 'table { width: 100%; border-collapse: collapse; }';
echo 'th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }';
echo 'tr:nth-child(even) { background-color: #f2f2f2; }';
echo 'th { background-color: #4CAF50; color: white; }';
echo '.header { display: flex; align-items: center; margin-bottom: 20px; }';
echo '.header img { height: 50px; margin-right: 15px; }'; /* Ajusta la altura y el margen como prefieras */
echo '.header h1 { margin: 0; }'; /* Elimina el margen superior e inferior del título para una mejor alineación */
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="header">';
echo '<img src="./logo.png" alt="Logo">'; 
echo '<h1>Datos de Alumnos</h1>';
echo '</div>';


if ($result->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>Legajo</th><th>Apellido</th><th>Nombre</th><th>Materia</th><th>Nota</th></tr>';
    
    // Salida de cada fila de datos
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row["legajo"] . '</td>';
        echo '<td>' . $row["apellido"] . '</td>';
        echo '<td>' . $row["nombre"] . '</td>';
        echo '<td>' . $row["materia"] . '</td>';
        echo '<td>' . $row["nota"] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo '0 resultados';
}

echo '</body>';
echo '</html>';

}
 
$conn->close();
?>

