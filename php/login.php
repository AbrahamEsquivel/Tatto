<?php
// ¡Paso clave! Inicia el sistema de Sesiones
session_start(); 

include 'conexion.php';

// 1. Verificar que los datos llegaron por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = $_POST['email'];
    $password_ingresada = $_POST['password'];

    // 2. Consulta para buscar al artista por su email
    $sql = "SELECT 
                a.id AS artista_id, 
                a.nombre_artistico, 
                a.password AS password_hash,
                p.email
            FROM ARTISTA AS a
            JOIN PERSONA AS p ON a.id_persona = p.id
            WHERE p.email = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        // 3. Si encontramos al usuario, verificar la contraseña
        $fila = $resultado->fetch_assoc();
        $password_hash_db = $fila['password_hash'];

        if (password_verify($password_ingresada, $password_hash_db)) {
            // ¡CONTRASEÑA CORRECTA!
            
            // 4. Guardar los datos en la "Pulsera VIP" (la Sesión)
            $_SESSION['logueado'] = true;
            $_SESSION['id_artista'] = $fila['artista_id'];
            $_SESSION['nombre_artista'] = $fila['nombre_artistico'];

            // 5. Redirigir a la Zona VIP (agenda.php)
            header('Location: ../agenda.php');
            exit;

        } else {
            // Contraseña incorrecta
            header('Location: ../login.html?error=1');
            exit;
        }

    } else {
        // Email no encontrado
        header('Location: ../login.html?error=1');
        exit;
    }
} else {
    // Si alguien intenta entrar a login.php directo, lo regresamos
    header('Location: ../login.html');
    exit;
}

?>