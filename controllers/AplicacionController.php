<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Aplicacion;
use Model\Usuario;

class AplicacionController extends ActiveRecord
{
    
    public static function renderizarPagina(Router $router)
    {
        $router->render('aplicaciones/index', []);
    }
    
    public static function guardarAPI()
    {
        getHeadersApi();

        try {
            // Validar campos obligatorios
            $_POST['apl_nombre'] = trim(htmlspecialchars($_POST['apl_nombre']));
            
            if (empty($_POST['apl_nombre'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre de la aplicación es obligatorio'
                ]);
                exit;
            }
            
            if (strlen($_POST['apl_nombre']) > 120) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre no puede exceder 120 caracteres'
                ]);
                exit;
            }

            $_POST['apl_fecha_inicio'] = trim($_POST['apl_fecha_inicio']);
            
            if (empty($_POST['apl_fecha_inicio'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La fecha de inicio es obligatoria'
                ]);
                exit;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['apl_fecha_inicio'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de fecha de inicio inválido. Use YYYY-MM-DD'
                ]);
                exit;
            }

            $_POST['apl_responsable'] = filter_var($_POST['apl_responsable'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['apl_responsable'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe asignar un responsable a la aplicación'
                ]);
                exit;
            }

            $_POST['apl_porcentaje_objetivo'] = filter_var($_POST['apl_porcentaje_objetivo'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['apl_porcentaje_objetivo'] < 0 || $_POST['apl_porcentaje_objetivo'] > 100) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El porcentaje objetivo debe estar entre 0 y 100'
                ]);
                exit;
            }

            // Verificar si ya existe una aplicación con el mismo nombre
            $sql_verificar = "SELECT COUNT(*) as total 
                             FROM aplicacion 
                             WHERE apl_nombre = " . self::$db->quote($_POST['apl_nombre']) . " 
                             AND apl_situacion = 1";
            
            $existe = self::fetchFirst($sql_verificar);
            
            if ($existe && $existe['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Ya existe una aplicación con ese nombre'
                ]);
                exit;
            }

            // Validar fechas si se proporciona fecha fin
            if (!empty($_POST['apl_fecha_fin'])) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['apl_fecha_fin'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Formato de fecha de fin inválido. Use YYYY-MM-DD'
                    ]);
                    exit;
                }
                
                if (strtotime($_POST['apl_fecha_fin']) < strtotime($_POST['apl_fecha_inicio'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La fecha de fin no puede ser anterior a la fecha de inicio'
                    ]);
                    exit;
                }
            }

            $_POST['apl_descripcion'] = trim(htmlspecialchars($_POST['apl_descripcion']));
            $_POST['apl_estado'] = $_POST['apl_estado'] ?? 'EN_PLANIFICACION';

            // Validar estado
            $estados_validos = ['EN_PLANIFICACION', 'EN_PROGRESO', 'PAUSADO', 'CERRADO'];
            if (!in_array($_POST['apl_estado'], $estados_validos)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado de aplicación no válido'
                ]);
                exit;
            }

            // Inserción
            $sql = "INSERT INTO aplicacion (
                        apl_nombre, apl_descripcion, apl_fecha_inicio, apl_fecha_fin,
                        apl_porcentaje_objetivo, apl_estado, apl_responsable, 
                        apl_creado_en, apl_situacion
                    ) VALUES (
                        " . self::$db->quote($_POST['apl_nombre']) . ",
                        " . self::$db->quote($_POST['apl_descripcion']) . ",
                        " . self::$db->quote($_POST['apl_fecha_inicio']) . ",
                        " . (!empty($_POST['apl_fecha_fin']) ? self::$db->quote($_POST['apl_fecha_fin']) : 'NULL') . ",
                        " . (int)$_POST['apl_porcentaje_objetivo'] . ",
                        " . self::$db->quote($_POST['apl_estado']) . ",
                        " . (int)$_POST['apl_responsable'] . ",
                        CURRENT,
                        1
                    )";
            
            $resultado = self::$db->exec($sql);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Aplicación registrada correctamente',
                    'data' => [
                        'id' => self::$db->lastInsertId()
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar la aplicación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarAplicacionesAPI()
    {
        try {
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
            $limite = isset($_GET['limite']) ? $_GET['limite'] : 100;
            
            $where = "WHERE a.apl_situacion = 1";
            
            if ($usuario_id) {
                $where .= " AND a.apl_responsable = $usuario_id";
            }
            
            if ($estado) {
                $where .= " AND a.apl_estado = '$estado'";
            }
            
            $sql = "SELECT a.apl_id, a.apl_nombre, a.apl_descripcion, a.apl_estado, 
                           a.apl_fecha_inicio, a.apl_fecha_fin, a.apl_porcentaje_objetivo,
                           a.apl_responsable, a.apl_creado_en,
                           u.usu_nombre as responsable_nombre, u.usu_grado
                    FROM aplicacion a
                    LEFT JOIN usuario u ON a.apl_responsable = u.usu_id
                    $where
                    ORDER BY a.apl_estado, a.apl_nombre
                    FIRST $limite";
            
            $aplicaciones = self::fetchArray($sql);
            
            // Agregar información adicional a cada aplicación
            foreach ($aplicaciones as &$app) {
                $app['ultimo_porcentaje'] = Aplicacion::obtenerUltimoPorcentaje($app['apl_id']);
                
                // Calcular días desde última actualización
                $sql_ultimo_reporte = "SELECT ava_fecha 
                                      FROM avance_diario 
                                      WHERE ava_apl_id = {$app['apl_id']} 
                                      AND ava_situacion = 1 
                                      ORDER BY ava_fecha DESC 
                                      FIRST 1";
                $ultimo_reporte = self::fetchFirst($sql_ultimo_reporte);
                
                if ($ultimo_reporte) {
                    $fecha_ultimo = strtotime($ultimo_reporte['ava_fecha']);
                    $fecha_hoy = strtotime(date('Y-m-d'));
                    $app['dias_sin_reporte'] = ($fecha_hoy - $fecha_ultimo) / (60 * 60 * 24);
                } else {
                    $app['dias_sin_reporte'] = 999; // Nunca ha tenido reporte
                }
            }
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Aplicaciones obtenidas correctamente',
                'data' => $aplicaciones
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener aplicaciones',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        try {
            $id = filter_var($_POST['apl_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'ID de aplicación inválido'
                ]);
                exit;
            }

            $aplicacion_actual = Aplicacion::find($id);
            
            if (!$aplicacion_actual) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Aplicación no encontrada'
                ]);
                exit;
            }

            // Validar campos obligatorios
            $_POST['apl_nombre'] = trim(htmlspecialchars($_POST['apl_nombre']));
            
            if (empty($_POST['apl_nombre'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre de la aplicación es obligatorio'
                ]);
                exit;
            }
            
            if (strlen($_POST['apl_nombre']) > 120) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre no puede exceder 120 caracteres'
                ]);
                exit;
            }

            $_POST['apl_fecha_inicio'] = trim($_POST['apl_fecha_inicio']);
            
            if (empty($_POST['apl_fecha_inicio'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La fecha de inicio es obligatoria'
                ]);
                exit;
            }

            $_POST['apl_responsable'] = filter_var($_POST['apl_responsable'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['apl_responsable'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe asignar un responsable a la aplicación'
                ]);
                exit;
            }

            $_POST['apl_porcentaje_objetivo'] = filter_var($_POST['apl_porcentaje_objetivo'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['apl_porcentaje_objetivo'] < 0 || $_POST['apl_porcentaje_objetivo'] > 100) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El porcentaje objetivo debe estar entre 0 y 100'
                ]);
                exit;
            }

            // Verificar si ya existe una aplicación con el mismo nombre (excluyendo la actual)
            $sql_verificar = "SELECT COUNT(*) as total 
                             FROM aplicacion 
                             WHERE apl_nombre = " . self::$db->quote($_POST['apl_nombre']) . " 
                             AND apl_situacion = 1 
                             AND apl_id != $id";
            
            $existe = self::fetchFirst($sql_verificar);
            
            if ($existe && $existe['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Ya existe una aplicación con ese nombre'
                ]);
                exit;
            }

            $_POST['apl_descripcion'] = trim(htmlspecialchars($_POST['apl_descripcion']));
            
            // Validar estado
            $estados_validos = ['EN_PLANIFICACION', 'EN_PROGRESO', 'PAUSADO', 'CERRADO'];
            if (!in_array($_POST['apl_estado'], $estados_validos)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado de aplicación no válido'
                ]);
                exit;
            }

            // Actualización
            $sql = "UPDATE aplicacion SET 
                        apl_nombre = " . self::$db->quote($_POST['apl_nombre']) . ",
                        apl_descripcion = " . self::$db->quote($_POST['apl_descripcion']) . ",
                        apl_fecha_inicio = " . self::$db->quote($_POST['apl_fecha_inicio']) . ",
                        apl_fecha_fin = " . (!empty($_POST['apl_fecha_fin']) ? self::$db->quote($_POST['apl_fecha_fin']) : 'NULL') . ",
                        apl_porcentaje_objetivo = " . (int)$_POST['apl_porcentaje_objetivo'] . ",
                        apl_estado = " . self::$db->quote($_POST['apl_estado']) . ",
                        apl_responsable = " . (int)$_POST['apl_responsable'] . "
                    WHERE apl_id = $id";
            
            $resultado = self::$db->exec($sql);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Aplicación actualizada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al actualizar la aplicación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function eliminarAPI()
    {
        getHeadersApi();

        try {
            $id = filter_var($_POST['apl_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'ID de aplicación inválido'
                ]);
                exit;
            }

            $aplicacion = Aplicacion::find($id);
            
            if (!$aplicacion) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Aplicación no encontrada'
                ]);
                exit;
            }

            // Verificar si tiene reportes asociados
            $sql_verificar = "SELECT COUNT(*) as total 
                             FROM avance_diario 
                             WHERE ava_apl_id = $id 
                             AND ava_situacion = 1";
            
            $tiene_reportes = self::fetchFirst($sql_verificar);
            
            if ($tiene_reportes && $tiene_reportes['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se puede eliminar la aplicación porque tiene reportes asociados'
                ]);
                exit;
            }

            $resultado = Aplicacion::EliminarAplicacion($id);
            
            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Aplicación eliminada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar la aplicación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function cambiarEstadoAPI()
    {
        getHeadersApi();
        
        try {
            $id = filter_var($_POST['apl_id'], FILTER_SANITIZE_NUMBER_INT);
            $nuevo_estado = trim($_POST['apl_estado']);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'ID de aplicación inválido'
                ]);
                exit;
            }
            
            $estados_validos = ['EN_PLANIFICACION', 'EN_PROGRESO', 'PAUSADO', 'CERRADO'];
            
            if (!in_array($nuevo_estado, $estados_validos)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado de aplicación no válido'
                ]);
                exit;
            }
            
            $sql = "UPDATE aplicacion SET apl_estado = '$nuevo_estado' WHERE apl_id = $id";
            $resultado = self::$db->exec($sql);
            
            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Estado actualizado correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al actualizar el estado'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarEstadisticasAPI()
    {
        try {
            // Estadísticas generales
            $sql_total = "SELECT COUNT(*) as total FROM aplicacion WHERE apl_situacion = 1";
            $total_result = self::fetchFirst($sql_total);
            
            $sql_por_estado = "SELECT apl_estado, COUNT(*) as total 
                              FROM aplicacion 
                              WHERE apl_situacion = 1 
                              GROUP BY apl_estado";
            $estados_result = self::fetchArray($sql_por_estado);
            
            $estadisticas = [
                'total' => $total_result ? $total_result['total'] : 0,
                'en_planificacion' => 0,
                'en_progreso' => 0,
                'pausadas' => 0,
                'cerradas' => 0
            ];
            
            foreach ($estados_result as $estado) {
                switch ($estado['apl_estado']) {
                    case 'EN_PLANIFICACION':
                        $estadisticas['en_planificacion'] = $estado['total'];
                        break;
                    case 'EN_PROGRESO':
                        $estadisticas['en_progreso'] = $estado['total'];
                        break;
                    case 'PAUSADO':
                        $estadisticas['pausadas'] = $estado['total'];
                        break;
                    case 'CERRADO':
                        $estadisticas['cerradas'] = $estado['total'];
                        break;
                }
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticas
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las estadísticas',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarUsuariosAPI()
    {
        try {
            $sql = "SELECT usu_id, usu_nombre, usu_grado, usu_email 
                    FROM usuario 
                    WHERE usu_situacion = 1 
                    AND usu_activo = 't'
                    ORDER BY usu_nombre";
            
            $usuarios = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios obtenidos correctamente',
                'data' => $usuarios
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los usuarios',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}