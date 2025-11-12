<?php
// Incluimos el header ANTES de hacer la consulta
include 'admin_header.php';

include 'php/conexion.php';
// Incluimos la conexión

// 2. OBTENER EL ID DE LA CITA ( DE LA URL )
if ( !isset( $_GET[ 'id' ] ) || !filter_var( $_GET[ 'id' ], FILTER_VALIDATE_INT ) ) {
    echo '<h1>Error: ID de cita no válido.</h1>';
    include 'admin_footer.php';
    // Cerramos la página
    exit;
}
$id_cita_a_editar = $_GET[ 'id' ];

// 3. BUSCAR LOS DATOS USANDO LA VISTA
$sql = 'SELECT * FROM v_AgendaCompleta WHERE id_cita = ? LIMIT 1';

$stmt = $conexion->prepare( $sql );
$stmt->bind_param( 'i', $id_cita_a_editar );
$stmt->execute();
$resultado = $stmt->get_result();

if ( $resultado->num_rows == 0 ) {
    echo '<h1>Error: Cita no encontrada.</h1>';
    include 'admin_footer.php';
    // Cerramos la página
    exit;
}

$cita = $resultado->fetch_assoc();
$stmt->close();

// Cargar catálogos para los <select>
$estados = $conexion->query( 'SELECT id, nombre FROM estado_cita ORDER BY nombre' );
$artistas = $conexion->query( 'SELECT id, nombre_artistico FROM artista ORDER BY nombre_artistico' );
$estilos_tatuaje = $conexion->query( 'SELECT id, nombre FROM estilo_tatuaje ORDER BY nombre' );

$conexion->close();
?>

<title>Editar Cita ID: <?php echo $cita[ 'id_cita' ];
?></title>

<style>
.form-container {

    max-width: 800px;

    margin: 0 auto;
    /* Centrado dentro del admin-content */
    background: #fff;

    padding: 2rem;

    border-radius: 10px;

    box-shadow: 0 4px 12px rgba( 0, 0, 0, 0.1 );

}
.form-container h1 {
    text-align: center;
    margin-bottom: 1.5rem;
}
.form-container .form-group {
    margin-bottom: 1rem;
}
.form-container .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.form-container .form-group input,
.form-container .form-group textarea,
.form-container .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}
.form-container .form-group input:disabled {
    background: #eee;
}
.form-container .btn {
    width: 100%;
    padding: 10px;
    background: #3B82F6;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}
.form-container .btn:hover {
    background: #2563EB;
}
</style>

<div class = 'form-container'>
<form id = 'form-editar-cita' action = 'php/updateCita.php' method = 'POST'>

<h1>Editando Cita #<?php echo htmlspecialchars( $cita[ 'id_cita' ] );
?></h1>

<input type = 'hidden' name = 'id_cita' value = "<?php echo $cita['id_cita']; ?>">
<input type = 'hidden' name = 'id_tatuaje' value = "<?php echo $cita['id_tatuaje']; ?>">

<h3>Datos del Cliente ( Informativo )</h3>
<div class = 'form-group'>
<label>Nombre:</label>
<input type = 'text' value = "<?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?>" disabled>
</div>
<div class = 'form-group'>
<label>Email:</label>
<input type = 'email' value = "<?php echo htmlspecialchars($cita['cliente_email']); ?>" disabled>
</div>

<hr style = 'margin: 20px 0;'>

<h3>Datos de la Cita ( Editables )</h3>
<div class = 'form-group'>
<label for = 'fecha_hora'>Fecha y Hora de la Cita:</label>
<input type = 'datetime-local' id = 'fecha_hora' name = 'fecha_hora' value = "<?php echo str_replace(' ', 'T', $cita['fecha_hora']); ?>" required>
</div>
<div class = 'form-group'>
<label for = 'id_artista'>Artista Asignado:</label>
<select id = 'id_artista' name = 'id_artista' required>
<?php while( $row = $artistas->fetch_assoc() ): ?>
<option value = "<?php echo $row['id']; ?>" <?php if ( $row[ 'id' ] == $cita[ 'id_artista' ] ) echo 'selected';
?>>
<?php echo htmlspecialchars( $row[ 'nombre_artistico' ] );
?>
</option>
<?php endwhile;
?>
</select>
</div>
<div class = 'form-group'>
<label for = 'id_estado_cita'>Estado de la Cita:</label>
<select id = 'id_estado_cita' name = 'id_estado_cita' required>
<?php while( $row = $estados->fetch_assoc() ): ?>
<option value = "<?php echo $row['id']; ?>" <?php if ( $row[ 'id' ] == $cita[ 'id_estado_cita' ] ) echo 'selected';
?>>
<?php echo htmlspecialchars( $row[ 'nombre' ] );
?>
</option>
<?php endwhile;
?>
</select>
</div>

<hr style = 'margin: 20px 0;'>

<h3>Datos del Tatuaje ( Editables )</h3>
<div class = 'form-group'>
<label for = 'id_estilo'>Estilo de Tatuaje:</label>
<select id = 'id_estilo' name = 'id_estilo' required>
<?php while( $row = $estilos_tatuaje->fetch_assoc() ): ?>
<option value = "<?php echo $row['id']; ?>" <?php if ( $row[ 'id' ] == $cita[ 'id_estilo' ] ) echo 'selected';
?>>
<?php echo htmlspecialchars( $row[ 'nombre' ] );
?>
</option>
<?php endwhile;
?>
</select>
</div>
<div class = 'form-group'>
<label for = 'precio_total'>Precio Total Acordado ( MXN ):</label>
<input type = 'number' id = 'precio_total' name = 'precio_total' step = '50' min = '0' value = "<?php echo htmlspecialchars($cita['precio_total']); ?>">
</div>

<button type = 'submit' class = 'btn'>Guardar Cambios</button>

<a href="pago-form.php?id_cita=<?php echo $cita['id_cita']; ?>&monto=<?php echo htmlspecialchars($cita['precio_total']); ?>" class="btn btn-verde" ...>
    + Registrar Pago para esta Cita
</a>

</form>
</div>

<style>
.btn.btn-verde {
    background-color: #28a745;
    /* Verde */
    display: block;
    /* Para que ocupe todo el ancho */
    text-decoration: none;
}
.btn.btn-verde:hover {
    background-color: #218838;
}
</style>

<?php
// Incluimos el footer
include 'admin_footer.php';
?>