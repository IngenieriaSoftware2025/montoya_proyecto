<?php
namespace Model;

use Exception;
use PDO;
class ActiveRecord {

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    protected static $idTabla = '';

    // Alertas y Mensajes
    protected static $alertas = [];
    
    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database) {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }
    // Validación
    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    // Registros - CRUD
    public function guardar() {
        $resultado = '';
        $id = static::$idTabla ?? 'id';
        if(!is_null($this->$id)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    public static function all() {
        $query = "SELECT * FROM " . static::$tabla;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busca un registro por su id
    public static function find($id = []) {
        $idQuery = static::$idTabla ?? 'id';
        $query = "SELECT * FROM " . static::$tabla ;

        if(is_array(static::$idTabla)){
            foreach (static::$idTabla as $key => $value) {
                if($value == reset(static::$idTabla)){
                    $query.= " WHERE $value = " . self::$db->quote( $id[$value] );
                }else{
                    $query.= " AND $value = " . self::$db->quote($id[$value] );

                }
            }
        }else{

           $query.= " WHERE $idQuery = $id";
        }
                
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    // Obtener Registro
    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT ${limite}";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    // Busqueda Where con Columna 
    public static function where($columna, $valor, $condicion = '=') {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} ${condicion} '${valor}'";
        $resultado = self::consultarSQL($query);
        return  $resultado ;
    }

    // SQL para Consultas Avanzadas.
    public static function SQL($consulta) {
        $query = $consulta;
        $resultado = self::$db->query($query);
        return $resultado;
    }

    // crea un nuevo registro
    public function crear() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Insertar en la base de datos
        $query = " INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES ("; 
        $query .= join(", ", array_values($atributos));
        $query .= " ) ";
        
        // Resultado de la consulta
        $resultado = self::$db->exec($query);

        return [
           'resultado' =>  $resultado,
           'id' => self::$db->lastInsertId()
        ];
    }

    public function actualizar() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "{$key}={$value}";
        }
        $id = static::$idTabla ?? 'id';

        $query = "UPDATE " . static::$tabla ." SET ";
        $query .=  join(', ', $valores );

        if(is_array(static::$idTabla)){

            foreach (static::$idTabla as $key => $value) {
                if($value == reset(static::$idTabla)){
                    $query.= " WHERE $value = " . self::$db->quote( $this->$value );
                }else{
                    $query.= " AND $value = " . self::$db->quote($this->$value );

                }
            }
        }else{
            $query .= " WHERE " . $id . " = " . self::$db->quote($this->$id) . " ";
            
        }

        $resultado = self::$db->exec($query);
        return [
            'resultado' =>  $resultado,
        ];
    }

    // Eliminar un registro - Toma el ID de Active Record
    public function eliminar() {
        $idQuery = static::$idTabla ?? 'id';
        $query = "DELETE FROM "  . static::$tabla . " WHERE $idQuery = " . self::$db->quote($this->id);
        $resultado = self::$db->exec($query);
        return $resultado;
    }

    public static function consultarSQL($query) {
        // Consultar la base de datos
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while($registro = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $array[] = static::crearObjeto($registro);
        }

        // liberar la memoria
        $resultado->closeCursor();

        // retornar los resultados
        return $array;
    }

    public static function fetchArray($query){
        try {
            $resultado = self::$db->query($query);
            $respuesta = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            if ($respuesta) {
                foreach ($respuesta as $value) {
                    $data[] = array_change_key_case( array_map( 'utf8_encode', $value) ); 
                }
            }
            $resultado->closeCursor();
            return $data;
        } catch (Exception $e) {
            error_log("Error en fetchArray: " . $e->getMessage());
            return [];
        }
    }

        
    public static function fetchFirst($query){
        try {
            $resultado = self::$db->query($query);
            $respuesta = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            if ($respuesta) {
                foreach ($respuesta as $value) {
                    $data[] = array_change_key_case( array_map( 'utf8_encode', $value) ); 
                }
            }
            $resultado->closeCursor();
            return array_shift($data);
        } catch (Exception $e) {
            error_log("Error en fetchFirst: " . $e->getMessage());
            return null;
        }
    }

    protected static function crearObjeto($registro) {
        $objeto = new static;

        foreach($registro as $key => $value ) {
            $key = strtolower($key);
            if(property_exists( $objeto, $key  )) {
                $objeto->$key = utf8_encode($value);
            }
        }

        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            $columna = strtolower($columna);
            if($columna === 'id' || $columna === static::$idTabla) continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }


    public static function validarFecha($fecha) {
    // Verificar formato YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return false;
    }
    
    // Verificar que sea una fecha válida
    $fecha_parts = explode('-', $fecha);
    return checkdate($fecha_parts[1], $fecha_parts[2], $fecha_parts[0]);
}


public static function fechaActual() {
    return date('Y-m-d');
}

// MÉTODO PARA VERIFICAR SI ES DÍA HÁBIL
public static function esDiaHabil($fecha = null) {
    if (!$fecha) {
        $fecha = self::fechaActual();
    }
    
    $dia_semana = date('N', strtotime($fecha)); // 1=Lunes, 7=Domingo
    return ($dia_semana >= 1 && $dia_semana <= 5); // Lunes a Viernes
}

public function sanitizarAtributos() {
    $atributos = $this->atributos();
    $sanitizado = [];
    
    foreach($atributos as $key => $value ) {
        // Manejo especial para campos de fecha/hora en Informix
        if (($key === 'ava_creado_en' || $key === 'ina_creado_en' || 
             $key === 'apl_creado_en' || strpos($key, '_creado_en') !== false) && empty($value)) {
            // Para campos de timestamp, usar CURRENT
            $sanitizado[$key] = 'CURRENT';
        } elseif (strpos($key, '_fecha') !== false && !empty($value)) {
            // Para campos de fecha simples, validar formato y usar directamente
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                // Es una fecha en formato ISO, usar directamente
                $sanitizado[$key] = "'" . $value . "'";
            } else {
                // Si no es formato ISO, intentar convertir
                $timestamp = strtotime($value);
                if ($timestamp !== false) {
                    $sanitizado[$key] = "'" . date('Y-m-d', $timestamp) . "'";
                } else {
                    // Si no se puede convertir, usar como está con quote
                    $sanitizado[$key] = self::$db->quote($value);
                }
            }
        } else {
            // Para todos los demás campos, usar quote normal
            $sanitizado[$key] = self::$db->quote($value);
        }
    }
    
    return $sanitizado;
}

    public function sincronizar($args=[]) { 
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}