<?php 
require_once __DIR__ . '/../includes/app.php';


use MVC\Router;
use Controllers\AppController;
use Controllers\AvanceDiarioController;
use Controllers\DashboardDesarrolladorController;
use Controllers\InactividadDiariaController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

$router->get('/', [AppController::class,'index']);


// Página principal del desarrollador
$router->get('/desarrollador', [DashboardDesarrolladorController::class, 'renderizarPagina']);

// APIs del desarrollador
$router->get('/API/desarrollador/aplicaciones', [DashboardDesarrolladorController::class, 'buscarAplicacionesAPI']);
$router->get('/API/desarrollador/calendario', [DashboardDesarrolladorController::class, 'buscarCalendarioAPI']);
$router->get('/API/desarrollador/resumen', [DashboardDesarrolladorController::class, 'buscarResumenAPI']);
$router->get('/API/desarrollador/verificarDia', [DashboardDesarrolladorController::class, 'verificarDiaHabilAPI']);

// =====================================
// RUTAS PARA AVANCE DIARIO
// =====================================

// Página de gestión de avances (opcional)
$router->get('/avance', [AvanceDiarioController::class, 'renderizarPagina']);

// APIs de avance diario
$router->post('/API/avance/guardar', [AvanceDiarioController::class, 'guardarAPI']);
$router->get('/API/avance/buscar', [AvanceDiarioController::class, 'buscarAPI']);
$router->post('/API/avance/modificar', [AvanceDiarioController::class, 'modificarAPI']);
$router->get('/API/avance/estadisticas', [AvanceDiarioController::class, 'buscarEstadisticasAPI']);
$router->get('/API/avance/diasSinReporte', [AvanceDiarioController::class, 'buscarDiasSinReporteAPI']);

//=========================================

$router->get('/API/desarrollador/debug', [DashboardDesarrolladorController::class, 'debugDatosAPI']);

$router->get('/API/desarrollador/debugFechas', [DashboardDesarrolladorController::class, 'debugFechasAPI']);

$router->get('/API/desarrollador/debugEspecifico', [DashboardDesarrolladorController::class, 'debugEspecificoAPI']);
// =====================================
// RUTAS PARA INACTIVIDAD DIARIA
// =====================================

// Página de gestión de inactividades (opcional)
$router->get('/inactividad', [InactividadDiariaController::class, 'renderizarPagina']);

// APIs de inactividad diaria
$router->post('/API/inactividad/guardar', [InactividadDiariaController::class, 'guardarAPI']);
$router->get('/API/inactividad/buscar', [InactividadDiariaController::class, 'buscarAPI']);
$router->post('/API/inactividad/modificar', [InactividadDiariaController::class, 'modificarAPI']);
$router->get('/API/inactividad/tipos', [InactividadDiariaController::class, 'buscarTiposAPI']);
$router->get('/API/inactividad/estadisticas', [InactividadDiariaController::class, 'buscarEstadisticasAPI']);
$router->get('/API/inactividad/verificarEstado', [InactividadDiariaController::class, 'verificarEstadoDiaAPI']);

// =====================================
// RUTAS PARA APLICACIONES (Si no existen)
// =====================================

// Página de gestión de aplicaciones (opcional)
$router->get('/aplicaciones', [AplicacionController::class, 'renderizarPagina']);

// APIs de aplicaciones
$router->post('/API/aplicaciones/guardar', [AplicacionController::class, 'guardarAPI']);
$router->get('/API/aplicaciones/buscar', [AplicacionController::class, 'buscarAPI']);
$router->post('/API/aplicaciones/modificar', [AplicacionController::class, 'modificarAPI']);
$router->get('/API/aplicaciones/eliminar', [AplicacionController::class, 'eliminarAPI']);

$router->comprobarRutas();
