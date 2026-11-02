<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esClub ? 'Acceso para clubes' : 'Inicio de sesión'; ?> - YELL Industries</title>
    <link rel="stylesheet" href="publico/css/login.css?v=20261028-5">
</head>
<body>

<!-- Formulario de inicio de sesión con textos ajustados al tipo de acceso. -->
<div class="login">

    <img class="login-logo" src="publico/img/logo-yell.png" alt="YELL Industries" width="150" height="150">
    <h1><?php echo $esClub ? 'Acceso para clubes' : 'Inicio de sesión'; ?></h1>

    <form action="validar.php" method="POST">

        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit"><?php echo $esClub ? 'Ingresar' : 'Ingresar al panel'; ?></button>

    </form>

</div>

<!-- Restaura la preferencia visual guardada previamente en el navegador. -->
<script>
    document.body.classList.toggle(
        "modo-claro",
        localStorage.getItem("ligaModoClaro") === "true"
    );
</script>

</body>
</html>