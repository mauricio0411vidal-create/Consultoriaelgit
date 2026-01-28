<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Prueba POST</title>
</head>
<body>
  <h2>Prueba directa al endpoint</h2>

  <form method="POST" action="/consultoria/api/contacto/crear.php">
    <input type="text" name="nombre" placeholder="Nombre" required><br><br>
    <input type="email" name="correo" placeholder="Correo" required><br><br>
    <input type="text" name="telefono" placeholder="Teléfono"><br><br>
    <input type="text" name="empresa" placeholder="Empresa"><br><br>
    <textarea name="mensaje" placeholder="Mensaje"></textarea><br><br>
    <button type="submit">Enviar</button>
  </form>

</body>
</html>
