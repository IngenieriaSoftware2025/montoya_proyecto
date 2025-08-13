<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Visita;

class VisitaController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('visitas/index', []);
    }

    // Convertir fecha ISO a formato BD
    private static function convertirFechaParaBD($fecha_iso)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha_iso, $matches)) {
            $anio = $matches[1];
            $mes = (int)$matches[2];
            $dia = (int)$matches[3];
            return "$mes/$dia/$anio";
        }
        
        return $fecha_iso;
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        try {
            // Validar campos obligatorios
            $_POST['vis_apl_id'] = filter_var($_POST['vis_apl_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['vis_apl_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación válida'
                ]);
                exit;
            }

            $_POST['vis_creado_por'] = filter_var($_POST['vis_creado_por'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['vis_creado_por'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar un usuario válido'
                ]);
                exit;
            }

            // Manejo de fecha y hora
            $_POST['vis_fecha'] = trim(htmlspecialchars($_POST['vis_fecha']));
            $_POST['vis_hora'] = trim(htmlspecialchars($_POST['vis_hora']));
            
            if (empty($_POST['vis_fecha'])) {
                $_POST['vis_fecha'] = date('Y-m-d');
            }

            if (empty($_POST['vis_hora'])) {
                $_POST['vis_hora'] = date('H:i');
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['vis_fecha'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de fecha inválido. Use YYYY-MM-DD'
                ]);
                exit;
            }

            if (!preg_match('/^\d{2}:\d{2}$/', $_POST['vis_hora'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de hora inválido. Use HH:MM'
                ]);
                exit;
            }

            // Combinar fecha y hora en formato Informix
            $fecha_para_bd = self::convertirFechaParaBD($_POST['vis_fecha']);
            $fecha_hora_bd = $fecha_para_bd . ' ' . $_POST['vis_hora'];

            $_POST['vis_quien'] = trim(htmlspecialchars($_POST['vis_quien']));
            
            if (empty($_POST['vis_quien'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar quién realizó la visita'
                ]);
                exit;
            }
            
            if (strlen($_POST['vis_quien']) < 3) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre debe tener al menos 3 caracteres'
                ]);
                exit;
            }

            $_POST['vis_motivo'] = trim(htmlspecialchars($_POST['vis_motivo']));
            
            if (empty($_POST['vis_motivo'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar el motivo de la visita'
                ]);
                exit;
            }
            
            if (strlen($_POST['vis_motivo']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El motivo debe tener al menos 10 caracteres'
                ]);
                exit;
            }

            $_POST['vis_procedimiento'] = trim(htmlspecialchars($_POST['vis_procedimiento']));
            
            if (empty($_POST['vis_procedimiento'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe describir el procedimiento realizado'
                ]);
                exit;
            }
            
            if (strlen($_POST['vis_procedimiento']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El procedimiento debe tener al menos 10 caracteres'
                ]);
                exit;
            }

            $_POST['vis_solucion'] = trim(htmlspecialchars($_POST['vis_solucion']));
            $_POST['vis_observacion'] = trim(htmlspecialchars($_POST['vis_observacion']));

            // Validar conformidad
            $_POST['vis_conformidad'] = $_POST['vis_conformidad'] ?? 'f';
            if (!in_array($_POST['vis_conformidad'], ['t', 'f', '1', '0', 'true', 'false'])) {
                $_POST['vis_conformidad'] = 'f';
            }
            
            // Normalizar conformidad a boolean de Informix
            $conformidad = in_array($_POST['vis_conformidad'], ['t', '1', 'true']) ? 't' : 'f';

            // Inserción
            $sql = "INSERT INTO visita (
                        vis_apl_id, vis_fecha, vis_quien, vis_motivo, 
                        vis_procedimiento, vis_solucion, vis_observacion, 
                        vis_conformidad, vis_creado_por, vis_creado_en, vis_situacion
                    ) VALUES (
                        " . (int)$_POST['vis_apl_id'] . ",
                        '" . $fecha_hora_bd . "',
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

            if($resultado){
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Visita registrada correctamente',
                    'data' => [
                        'id' => self::$db->lastInsertId(),
                        'conformidad' => $conformidad
                    ]
                ]);
                exit;
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar la visita'
                ]);
                exit;
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
            exit;
        }
    }

    public static function buscarAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            $limite = isset($_GET['limite']) ? $_GET['limite'] : 50;
            $conformidad = isset($_GET['conformidad']) ? $_GET['conformidad'] : null;

            if ($apl_id && $fecha_inicio && $fecha_fin) {
                $data = Visita::obtenerPorRango($apl_id, $fecha_inicio, $fecha_fin, $conformidad);
            } elseif ($apl_id) {
                $data = Visita::obtenerHistorial($apl_id, $limite, $conformidad);
            } else {
                $data = Visita::obtenerTodas($limite, $conformidad);
            }

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

    public static function modificarAPI()
    {
        getHeadersApi();

        try {
            $id = $_POST['vis_id'];
            
            $visita_actual = Visita::find($id);
            
            if (!$visita_actual) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Visita no encontrada'
                ]);
                return;
            }

            // Verificar que pueda modificar (solo el mismo día)
            $fecha_visita = date('Y-m-d', strtotime($visita_actual->vis_fecha));
            $fecha_hoy = date('Y-m-d');
            
            if ($fecha_visita !== $fecha_hoy) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede modificar una visita del día actual'
                ]);
                return;
            }

            // Validaciones similares al guardar
            $_POST['vis_quien'] = trim(htmlspecialchars($_POST['vis_quien']));
            $_POST['vis_motivo'] = trim(htmlspecialchars($_POST['vis_motivo']));
            $_POST['vis_procedimiento'] = trim(htmlspecialchars($_POST['vis_procedimiento']));
            $_POST['vis_solucion'] = trim(htmlspecialchars($_POST['vis_solucion']));
            $_POST['vis_observacion'] = trim(htmlspecialchars($_POST['vis_observacion']));

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

            // Normalizar conformidad
            $conformidad = in_array($_POST['vis_conformidad'], ['t', '1', 'true']) ? 't' : 'f';

            $visita_actual->sincronizar([
                'vis_quien' => $_POST['vis_quien'],
                'vis_motivo' => $_POST['vis_motivo'],
                'vis_procedimiento' => $_POST['vis_procedimiento'],
                'vis_solucion' => $_POST['vis_solucion'],
                'vis_observacion' => $_POST['vis_observacion'],
                'vis_conformidad' => $conformidad,
                'vis_situacion' => 1
            ]);
            
            $visita_actual->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Visita modificada correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al modificar la visita',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarEstadisticasAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

            $estadisticas = Visita::obtenerEstadisticas($apl_id, $fecha_inicio, $fecha_fin);
            $resumen_conformidad = Visita::obtenerResumenConformidad($apl_id, $fecha_inicio, $fecha_fin);
            $tendencia_mensual = Visita::obtenerTendenciaMensual($apl_id);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'estadisticas' => $estadisticas,
                    'resumen_conformidad' => $resumen_conformidad,
                    'tendencia_mensual' => $tendencia_mensual
                ]
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
                    FROM aplicacion a
                    LEFT JOIN usuario u ON a.apl_responsable = u.usu_id
                    WHERE a.apl_situacion = 1 
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

            $visita = Visita::find($id);
            
            if (!$visita) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Visita no encontrada'
                ]);
                exit;
            }

            // Verificar que pueda eliminar (solo el mismo día)
            $fecha_visita = date('Y-m-d', strtotime($visita->vis_fecha));
            $fecha_hoy = date('Y-m-d');
            
            if ($fecha_visita !== $fecha_hoy) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede eliminar una visita del día actual'
                ]);
                exit;
            }

            $resultado = Visita::EliminarVisita($id);
            
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