<?php 
require_once __DIR__ . '/../includes/app.php';

use Controllers\AplicacionController;
use MVC\Router;
use Controllers\AppController;
use Controllers\AvanceDiarioController;
use Controllers\ComentarioController;
use Controllers\DashboardDesarrolladorController;
use Controllers\DashboardGerenteController;
use Controllers\InactividadDiariaController;
use Controllers\VisitaController;

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
// RUTAS PARA APLICACIONES
// =====================================

$router->get('/aplicaciones', [AplicacionController::class, 'renderizarPagina']);

// APIs para CRUD de aplicaciones
$router->post('/aplicaciones/guardarAPI', [AplicacionController::class, 'guardarAPI']);
$router->get('/aplicaciones/buscarAPI', [AplicacionController::class, 'buscarAplicacionesAPI']);
$router->post('/aplicaciones/modificarAPI', [AplicacionController::class, 'modificarAPI']);
$router->post('/aplicaciones/eliminarAPI', [AplicacionController::class, 'eliminarAPI']);

// APIs adicionales para aplicaciones
$router->post('/aplicaciones/cambiarEstadoAPI', [AplicacionController::class, 'cambiarEstadoAPI']);
$router->get('/aplicaciones/buscarEstadisticasAPI', [AplicacionController::class, 'buscarEstadisticasAPI']);
$router->get('/aplicaciones/buscarUsuariosAPI', [AplicacionController::class, 'buscarUsuariosAPI']);

// =====================================
// RUTAS PARA VISITAS
// =====================================

// Página de gestión de visitas
$router->get('/visitas', [VisitaController::class, 'renderizarPagina']);

// APIs de visitas (versión simplificada)
$router->post('/API/visitas/guardar', [VisitaController::class, 'guardarAPI']);
$router->get('/API/visitas/buscar', [VisitaController::class, 'buscarAPI']);
$router->post('/API/visitas/eliminar', [VisitaController::class, 'eliminarAPI']);
$router->post('/API/visitas/modificar', [VisitaController::class, 'modificarAPI']);
$router->get('/API/visitas/estadisticas', [VisitaController::class, 'buscarEstadisticasAPI']);
$router->get('/API/visitas/aplicaciones', [VisitaController::class, 'buscarAplicacionesAPI']);

// =====================================
// RUTAS PARA COMENTARIOS
// =====================================

// Página de gestión de comentarios
$router->get('/comentarios', [ComentarioController::class, 'renderizarPagina']);

// APIs de comentarios
$router->post('/API/comentarios/guardar', [ComentarioController::class, 'guardarAPI']);
$router->get('/API/comentarios/buscar', [ComentarioController::class, 'buscarAPI']);
$router->post('/API/comentarios/marcarLeido', [ComentarioController::class, 'marcarLeidoAPI']);
$router->post('/API/comentarios/marcarTodosLeidos', [ComentarioController::class, 'marcarTodosLeidosAPI']);
$router->post('/API/comentarios/eliminar', [ComentarioController::class, 'eliminarAPI']);
$router->get('/API/comentarios/estadisticas', [ComentarioController::class, 'obtenerEstadisticasAPI']);
$router->get('/API/comentarios/aplicaciones', [AplicacionController::class, 'buscarAplicacionesAPI']);

// Página principal del gerente
$router->get('/gerente', [DashboardGerenteController::class, 'renderizarPagina']);

// APIs del gerente
$router->get('/API/gerente/resumen', [DashboardGerenteController::class, 'buscarResumenEjecutivoAPI']);
$router->get('/API/gerente/aplicaciones', [DashboardGerenteController::class, 'buscarAplicacionesCompletas']);
$router->get('/API/gerente/graficos', [DashboardGerenteController::class, 'buscarDatosGraficosAPI']);
$router->get('/API/gerente/alertas', [DashboardGerenteController::class, 'buscarAlertasAPI']);
$router->get('/API/gerente/metricas', [DashboardGerenteController::class, 'buscarMetricasRendimientoAPI']);

$router->comprobarRutas();
