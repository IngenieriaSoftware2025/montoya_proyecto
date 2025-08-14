<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="build/js/app.js"></script>
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Monitoreo de Aplicación</title>
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="/montoya_proyecto/">
            <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="cit">
            Monitoreo de Aplicaciones
        </a>
        <div class="collapse navbar-collapse" id="navbarToggler">
            
            <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin: 0;">
                <!-- Dashboard Ejecutivo (Gerente) -->
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/montoya_proyecto/gerente">
                        <i class="bi bi-pie-chart-fill me-2"></i>Dashboard Ejecutivo
                    </a>
                </li>
                
                <!-- Dashboard Desarrollador -->
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/montoya_proyecto/desarrollador">
                        <i class="bi bi-code-square me-2"></i>Dashboard Desarrollador
                    </a>
                </li>

                <!-- Dropdown Gestión -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear-fill me-2"></i>Gestión
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" id="dropdownGestion" style="margin: 0;">
                        <h6 class="dropdown-header">Aplicaciones</h6>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/aplicaciones">
                                <i class="ms-lg-0 ms-2 bi bi-list-task me-2"></i>Ver Aplicaciones
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/aplicaciones/nueva">
                                <i class="ms-lg-0 ms-2 bi bi-plus-circle me-2"></i>Nueva Aplicación
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <h6 class="dropdown-header">Reportes</h6>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/avance">
                                <i class="ms-lg-0 ms-2 bi bi-bar-chart-line me-2"></i>Avances Diarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/inactividad">
                                <i class="ms-lg-0 ms-2 bi bi-pause-circle me-2"></i>Justificaciones
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <h6 class="dropdown-header">Comunicación</h6>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/comentarios">
                                <i class="ms-lg-0 ms-2 bi bi-chat-dots me-2"></i>Comentarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/visitas">
                                <i class="ms-lg-0 ms-2 bi bi-people me-2"></i>Visitas
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Dropdown Administración -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-shield-lock-fill me-2"></i>Administración
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" id="dropdownAdmin" style="margin: 0;">
                        <h6 class="dropdown-header">Usuarios</h6>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/usuarios">
                                <i class="ms-lg-0 ms-2 bi bi-person-lines-fill me-2"></i>Gestionar Usuarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/usuarios/nuevo">
                                <i class="ms-lg-0 ms-2 bi bi-person-plus me-2"></i>Nuevo Usuario
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <h6 class="dropdown-header">Configuración</h6>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/configuracion">
                                <i class="ms-lg-0 ms-2 bi bi-gear me-2"></i>Configuración Sistema
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item nav-link text-white" href="/montoya_proyecto/roles">
                                <i class="ms-lg-0 ms-2 bi bi-person-badge me-2"></i>Gestionar Roles
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Estadísticas -->
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/montoya_proyecto/estadisticas">
                        <i class="bi bi-graph-up me-2"></i>Estadísticas
                    </a>
                </li>
            </ul>
            
            <div class="col-lg-1 d-grid mb-lg-0 mb-2">
                <!-- Ruta relativa desde el archivo donde se incluye menu.php -->
                <a href="/menu/" class="btn btn-danger">
                    <i class="bi bi-arrow-bar-left"></i>MENÚ
                </a>
            </div>
        </div>
    </div>
</nav>

    <div class="progress fixed-bottom" style="height: 6px;">
        <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
        
        <?php echo $contenido; ?>
    </div>
    <div class="container-fluid " >
        <div class="row justify-content-center text-center">
            <div class="col-12">
                <p style="font-size:xx-small; font-weight: bold;">
                        Comando de Informática y Tecnología, <?= date('Y') ?> &copy;
                </p>
            </div>
        </div>
    </div>
</body>
</html>