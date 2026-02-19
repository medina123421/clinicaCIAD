<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
require_once 'app/includes/config.php';
// Esta ruta debe ser exacta a donde está tu archivo de base de datos
require_once 'app/config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Por favor, ingrese email y contraseña.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $query = "SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.email = :email AND u.activo = 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $usuario['password_hash'])) {
                    $_SESSION['usuario_id'] = $usuario['id_usuario'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido_paterno'];
                    $_SESSION['usuario_rol'] = $usuario['nombre_rol'];
                    header('Location: ' . PROJECT_PATH . '/index.php');
                    exit();
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'Usuario no encontrado.';
            }
        } catch (PDOException $e) {
            $error = 'Error de conexión.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - CIADI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #192d8e;
            --secondary-color: #0d6efd;
            --bg-gradient: linear-gradient(135deg, #192d8e 0%, #0d6efd 50%, #f0f4ff 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 90%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 40px 20px;
            text-align: center;
            color: #fff;
        }

        .login-header img {
            max-width: 120px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
            background: white;
            padding: 10px;
            border-radius: 50%;
        }

        .login-header h2 {
            font-weight: 700;
            letter-spacing: -1px;
            margin: 0;
        }

        .login-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #495057;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(25, 45, 142, 0.1);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }

        .input-group .form-control:not(:last-child) {
            border-radius: 0;
        }

        .input-group .btn-outline-secondary {
            border-radius: 0 10px 10px 0;
            border: 1px solid #dee2e6;
            border-left: none;
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .input-group .btn-outline-secondary:hover {
            background-color: #e9ecef;
            color: var(--primary-color);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 45, 142, 0.3);
            color: #fff;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .footer-text {
            text-align: center;
            font-size: 0.8rem;
            color: #adb5bd;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <img src="<?= PROJECT_PATH ?>/app/assets/img/logo_ciadi.png" alt="Logo CIADI">
            <h2>CIADI</h2>
            <p class="mb-0 opacity-75">Panel de Gestión Médica</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="ejemplo@clinica.com" required
                            autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••"
                            required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                    </button>
                </div>
            </form>

            <div class="footer-text">
                &copy; <?= date('Y') ?> CIADI. Todos los derechos reservados.
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye / eye slash icon
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
</body>

</html>