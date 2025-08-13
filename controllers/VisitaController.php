<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;

class VisitaController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('visitas/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        try {
            // Validación básica
            $_POST['vis_apl_id'] = filter_var($_POST['vis_apl_id'], FILTER_SANITIZE_NUMBER_INT);
            $_POST['vis_creado_por'] = filter_var($_POST['vis_creado_por'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['vis_apl_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación válida'
                ]);
                exit;
            }

            if ($_POST['vis_creado_por'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar un usuario válido'
                ]);
                exit;
            }

            // Campos de texto
            $_POST['vis_quien'] = trim(htmlspecialchars($_POST['vis_quien']));
            $_POST['vis_motivo'] = trim(htmlspecialchars($_POST['vis_motivo']));
            $_POST['vis_procedimiento'] = trim(htmlspecialchars($_POST['vis_procedimiento']));
            $_POST['vis_solucion'] = trim(htmlspecialchars($_POST['vis_solucion'] ?? ''));
            $_POST['vis_observacion'] = trim(htmlspecialchars($_POST['vis_observacion'] ?? ''));
            
            // Validaciones de texto
            if (empty($_POST['vis_quien']) || strlen($_POST['vis_quien']) < 3) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre del visitante debe tener al menos 3 caracteres'
                ]);
                exit;
            }

            if (empty($_POST['vis_motivo']) || strlen($_POST['vis_motivo']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El motivo debe tener al menos 10 caracteres'
                ]);
                exit;
            }

            if (empty($_POST['vis_procedimiento']) || strlen($_POST['vis_procedimiento']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El procedimiento debe tener al menos 10 caracteres'
                ]);
                exit;
            }

            // Fecha y hora
            $fecha = $_POST['vis_fecha'] ?? date('Y-m-d');
            $hora = $_POST['vis_hora'] ?? date('H:i');
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de fecha inválido'
                ]);
                exit;
            }

            if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de hora inválido'
                ]);
                exit;
            }

            $fecha_hora = $fecha . ' ' . $hora . ':00';

            // Conformidad (char 't' o 'f')
            $conformidad = ($_POST['vis_conformidad'] === 't' || $_POST['vis_conformidad'] === 'true') ? 't' : 'f';

            // Inserción
            $sql = "INSERT INTO visita (
                        vis_apl_id, vis_fecha, vis_quien, vis_motivo, 
                        vis_procedimiento, vis_solucion, vis_observacion, 
                        vis_conformidad, vis_creado_por, vis_creado_en, vis_situacion
                    ) VALUES (
                        " . (int)$_POST['vis_apl_id'] . ",
                        '" . $fecha_hora . "',
                        " . self::$db->quote($_POST['vis_quien']) . ",
                        " . self::$db->quote($_POST['vis_motivo']) . ",
                        " . self::$db->quote($_POST['vis_procedimiento']) . ",
                        " . self::$db->quote($_POST['vis_solucion']) . ",
                        " . self::$db->quote($_POST['vis_observacion']) . ",
                        '" . $conformidad . "',
                        " . (int)$_POST['vis_creado_por'] . ",
                        CURRENT,
                        1
                    )";
            
            $resultado = self::$db->exec($sql);

            if($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Visita registrada correctamente',
                    'data' => [
                        'id' => self::$db->lastInsertId(),
                        'conformidad' => $conformidad
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar la visita'
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

    public static function buscarAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $limite = isset($_GET['limite']) ? $_GET['limite'] : 50;
            $conformidad = isset($_GET['conformidad']) ? $_GET['conformidad'] : null;

            $where_clauses = ["v.vis_situacion = 1"];
            
            if ($apl_id) {
                $where_clauses[] = "v.vis_apl_id = $apl_id";
            }
            
            if ($conformidad !== null) {
                $conformidad_valor = ($conformidad === 'true' || $conformidad === '1') ? 't' : 'f';
                $where_clauses[] = "v.vis_conformidad = '$conformidad_valor'";
            }
            
            $where_clause = implode(' AND ', $where_clauses);
            
            $sql = "SELECT v.vis_id, v.vis_apl_id, v.vis_fecha, v.vis_quien, v.vis_motivo, 
                           v.vis_procedimiento, v.vis_solucion, v.vis_observacion, v.vis_conformidad,
                           a.apl_nombre, u.usu_nombre as creado_por_nombre, u.usu_grado
                    FROM visita v, aplicacion a, usuario u
                    WHERE v.vis_apl_id = a.apl_id 
                    AND v.vis_creado_por = u.usu_id
                    AND $where_clause
                    ORDER BY v.vis_fecha DESC, v.vis_id DESC
                    FIRST $limite";
            
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Visitas obtenidas correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las visitas',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarEstadisticasAPI()
    {
        try {
            // Consultas separadas para evitar problemas con CASE en Informix
            $sql_total = "SELECT COUNT(*) as total FROM visita WHERE vis_situacion = 1";
            $sql_conformes = "SELECT COUNT(*) as conformes FROM visita WHERE vis_situacion = 1 AND vis_conformidad = 't'";
            $sql_no_conformes = "SELECT COUNT(*) as no_conformes FROM visita WHERE vis_situacion = 1 AND vis_conformidad = 'f'";
            $sql_con_solucion = "SELECT COUNT(*) as con_solucion FROM visita WHERE vis_situacion = 1 AND vis_solucion IS NOT NULL AND LENGTH(TRIM(vis_solucion)) > 0";
            
            $total = self::fetchFirst($sql_total);
            $conformes = self::fetchFirst($sql_conformes);
            $no_conformes = self::fetchFirst($sql_no_conformes);
            $con_solucion = self::fetchFirst($sql_con_solucion);
            
            $estadisticas = [
                'total_visitas' => $total['total'],
                'visitas_conformes' => $conformes['conformes'],
                'visitas_no_conformes' => $no_conformes['no_conformes'],
                'visitas_con_solucion' => $con_solucion['con_solucion']
            ];

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => ['estadisticas' => $estadisticas]
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

    public static function buscarAplicacionesAPI()
    {
        try {
            $sql = "SELECT apl_id, apl_nombre, apl_estado, apl_responsable, 
                           u.usu_nombre as responsable_nombre, u.usu_grado
                    FROM aplicacion a, usuario u 
                    WHERE a.apl_responsable = u.usu_id
                    AND a.apl_situacion = 1 
                    ORDER BY a.apl_nombre";
            
            $aplicaciones = self::fetchArray($sql);

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
                'mensaje' => 'Error al obtener las aplicaciones',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        try {
            $id = $_POST['vis_id'];
            
            // Verificar que la visita existe
            $sql_check = "SELECT vis_id, vis_fecha FROM visita WHERE vis_id = $id AND vis_situacion = 1";
            $visita_actual = self::fetchFirst($sql_check);
            
            if (!$visita_actual) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Visita no encontrada'
                ]);
                return;
            }

            // Verificar que sea del día actual
            $fecha_visita = date('Y-m-d', strtotime($visita_actual['vis_fecha']));
            $fecha_hoy = date('Y-m-d');
            
            if ($fecha_visita !== $fecha_hoy) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede modificar una visita del día actual'
                ]);
                return;
            }

            // Validaciones
            $_POST['vis_quien'] = trim(htmlspecialchars($_POST['vis_quien']));
            $_POST['vis_motivo'] = trim(htmlspecialchars($_POST['vis_motivo']));
            $_POST['vis_procedimiento'] = trim(htmlspecialchars($_POST['vis_procedimiento']));
            $_POST['vis_solucion'] = trim(htmlspecialchars($_POST['vis_solucion'] ?? ''));
            $_POST['vis_observacion'] = trim(htmlspecialchars($_POST['vis_observacion'] ?? ''));

            if (empty($_POST['vis_quien']) || strlen($_POST['vis_quien']) < 3) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre debe tener al menos 3 caracteres'
                ]);
                return;
            }

            if (empty($_POST['vis_motivo']) || strlen($_POST['vis_motivo']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El motivo debe tener al menos 10 caracteres'
                ]);
                return;
            }

            if (empty($_POST['vis_procedimiento']) || strlen($_POST['vis_procedimiento']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El procedimiento debe tener al menos 10 caracteres'
                ]);
                return;
            }

            // Conformidad
            $conformidad = ($_POST['vis_conformidad'] === 't' || $_POST['vis_conformidad'] === 'true') ? 't' : 'f';

            // Actualización
            $sql = "UPDATE visita SET 
                        vis_quien = " . self::$db->quote($_POST['vis_quien']) . ",
                        vis_motivo = " . self::$db->quote($_POST['vis_motivo']) . ",
                        vis_procedimiento = " . self::$db->quote($_POST['vis_procedimiento']) . ",
                        vis_solucion = " . self::$db->quote($_POST['vis_solucion']) . ",
                        vis_observacion = " . self::$db->quote($_POST['vis_observacion']) . ",
                        vis_conformidad = '$conformidad'
                    WHERE vis_id = $id";
            
            $resultado = self::$db->exec($sql);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Visita modificada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al modificar la visita'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al modificar la visita',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function eliminarAPI()
    {
        getHeadersApi();

        try {
            $id = filter_var($_POST['vis_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'ID de visita inválido'
                ]);
                exit;
            }

            // Verificar que existe y es del día actual
            $sql_check = "SELECT vis_id, vis_fecha FROM visita WHERE vis_id = $id AND vis_situacion = 1";
            $visita = self::fetchFirst($sql_check);
            
            if (!$visita) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Visita no encontrada'
                ]);
                exit;
            }

            $fecha_visita = date('Y-m-d', strtotime($visita['vis_fecha']));
            $fecha_hoy = date('Y-m-d');
            
            if ($fecha_visita !== $fecha_hoy) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede eliminar una visita del día actual'
                ]);
                exit;
            }

            // Eliminación lógica
            $sql = "UPDATE visita SET vis_situacion = 0 WHERE vis_id = $id";
            $resultado = self::$db->exec($sql);
            
            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Visita eliminada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar la visita'
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
}