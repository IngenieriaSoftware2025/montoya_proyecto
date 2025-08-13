import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import dayjs from 'dayjs';
import 'dayjs/locale/es';

// Configurar dayjs en español
dayjs.locale('es');

// Paleta de colores para tema oscuro
const THEME_COLORS = {
    primary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    secondary: '#475569',
    dark: '#1e293b',
    darker: '#0f172a',
    tertiary: '#334155',
    text: '#f8fafc',
    textSecondary: '#cbd5e1',
    textMuted: '#94a3b8',
    border: '#374151'
};

// Variables globales
let aplicaciones = [];
let usuarios = [];
let aplicacionEditando = null;

// Configuración SweetAlert2 con tema oscuro
const swalConfig = {
    background: THEME_COLORS.dark,
    color: THEME_COLORS.text,
    confirmButtonColor: THEME_COLORS.primary,
    cancelButtonColor: THEME_COLORS.secondary,
    customClass: {
        popup: 'swal-dark-theme',
        title: 'swal-dark-title',
        content: 'swal-dark-content',
        confirmButton: 'btn-dark-theme',
        cancelButton: 'btn-dark-theme'
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    aplicarEstilosSwalOscuros();
    cargarUsuarios();
    cargarAplicaciones();
    cargarEstadisticas();
    configurarEventos();
});

// Aplicar estilos oscuros a SweetAlert2
const aplicarEstilosSwalOscuros = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-dark-theme {
            background-color: ${THEME_COLORS.dark} !important;
            color: ${THEME_COLORS.text} !important;
            border: 1px solid ${THEME_COLORS.border} !important;
        }
        
        .swal-dark-title {
            color: ${THEME_COLORS.text} !important;
        }
        
        .swal-dark-content {
            color: ${THEME_COLORS.textSecondary} !important;
        }
        
        .swal2-input, .swal2-textarea, .swal2-select {
            background-color: ${THEME_COLORS.tertiary} !important;
            border: 1px solid ${THEME_COLORS.border} !important;
            color: ${THEME_COLORS.text} !important;
        }
        
        .swal2-input:focus, .swal2-textarea:focus, .swal2-select:focus {
            border-color: ${THEME_COLORS.primary} !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        .btn-dark-theme {
            border-radius: 8px !important;
            font-weight: 500 !important;
        }
    `;
    document.head.appendChild(style);
};

// Configurar eventos
function configurarEventos() {
    // Formulario de aplicación
    const formAplicacion = document.getElementById('formAplicacion');
    if (formAplicacion) {
        formAplicacion.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarAplicacion();
        });
    }
}

// ================================
// FUNCIONES DE CARGA DE DATOS
// ================================

// Cargar usuarios para los selects
async function cargarUsuarios() {
    try {
        const response = await fetch('/montoya_proyecto/aplicaciones/buscarUsuariosAPI');
        const data = await response.json();
        
        if (data.codigo === 1) {
            usuarios = data.data;
            llenarSelectUsuarios();
        }
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
    }
}

// Llenar selects de usuarios
function llenarSelectUsuarios() {
    const selects = ['filtroResponsable', 'aplicacionResponsable'];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select && selectId !== 'filtroResponsable') {
            select.innerHTML = '<option value="">Seleccione un responsable...</option>';
        } else if (select) {
            select.innerHTML = '<option value="">Todos los responsables</option>';
        }
        
        usuarios.forEach(usuario => {
            const option = document.createElement('option');
            option.value = usuario.usu_id;
            option.textContent = `${usuario.usu_grado || ''} ${usuario.usu_nombre}`.trim();
            select.appendChild(option);
        });
    });
}

// Cargar aplicaciones
async function cargarAplicaciones() {
    try {
        const response = await fetch('/montoya_proyecto/aplicaciones/buscarAPI');
        const data = await response.json();
        
        if (data.codigo === 1) {
            aplicaciones = data.data;
            mostrarAplicaciones(aplicaciones);
        } else {
            mostrarError('Error al cargar aplicaciones: ' + data.mensaje);
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
        mostrarError('Error al cargar las aplicaciones');
    }
}

// Cargar estadísticas
async function cargarEstadisticas() {
    try {
        const response = await fetch('/montoya_proyecto/aplicaciones/buscarEstadisticasAPI');
        const data = await response.json();
        
        if (data.codigo === 1) {
            const stats = data.data;
            document.getElementById('totalAplicaciones').textContent = stats.total;
            document.getElementById('enProgreso').textContent = stats.en_progreso;
            document.getElementById('pausadas').textContent = stats.pausadas;
            document.getElementById('enPlanificacion').textContent = stats.en_planificacion;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// ================================
// FUNCIONES DE VISUALIZACIÓN
// ================================

// Mostrar aplicaciones en la tabla
function mostrarAplicaciones(apps) {
    const tbody = document.getElementById('tablaAplicacionesBody');
    tbody.innerHTML = '';
    
    if (apps.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-cubes fa-2x mb-2"></i><br>
                    No hay aplicaciones registradas
                </td>
            </tr>
        `;
        return;
    }
    
    apps.forEach(app => {
        const row = `
            <tr>
                <td>
                    <strong>${app.apl_nombre}</strong>
                    ${app.apl_descripcion ? `<br><small class="text-muted">${app.apl_descripcion}</small>` : ''}
                </td>
                <td>
                    ${app.responsable_nombre || 'Sin asignar'}
                    ${app.usu_grado ? `<br><small class="text-muted">${app.usu_grado}</small>` : ''}
                </td>
                <td>
                    <span class="badge ${obtenerClaseEstado(app.apl_estado)}">
                        ${formatearEstado(app.apl_estado)}
                    </span>
                </td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${obtenerClaseProgreso(app.ultimo_porcentaje)}" 
                             role="progressbar" style="width: ${app.ultimo_porcentaje}%">
                            ${app.ultimo_porcentaje}%
                        </div>
                    </div>
                </td>
                <td>${formatearFecha(app.apl_fecha_inicio)}</td>
                <td>
                    ${app.dias_sin_reporte === 999 ? 
                        '<span class="badge bg-secondary">Sin reportes</span>' : 
                        app.dias_sin_reporte > 2 ? 
                            `<span class="badge bg-danger">${app.dias_sin_reporte} días</span>` :
                            app.dias_sin_reporte > 0 ?
                                `<span class="badge bg-warning">${app.dias_sin_reporte} días</span>` :
                                '<span class="badge bg-success">Hoy</span>'
                    }
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarAplicacion(${app.apl_id})" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="verDetalleAplicacion(${app.apl_id})" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="cambiarEstadoAplicacion(${app.apl_id}, '${app.apl_estado}')" title="Cambiar estado">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarAplicacion(${app.apl_id})" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

// ================================
// FUNCIONES DE FORMULARIO
// ================================

// Mostrar formulario para nueva aplicación
function mostrarFormularioNuevaAplicacion() {
    aplicacionEditando = null;
    document.getElementById('modalAplicacionTitulo').innerHTML = '<i class="bi bi-grid-3x3-gap"></i> Nueva Aplicación';
    document.getElementById('formAplicacion').reset();
    document.getElementById('aplicacionId').value = '';
    document.getElementById('aplicacionFechaInicio').value = dayjs().format('YYYY-MM-DD');
    
    const modal = new Modal(document.getElementById('modalAplicacion'));
    modal.show();
}

// Editar aplicación
function editarAplicacion(id) {
    const app = aplicaciones.find(a => a.apl_id == id);
    if (!app) return;
    
    aplicacionEditando = app;
    document.getElementById('modalAplicacionTitulo').innerHTML = '<i class="bi bi-pencil"></i> Editar Aplicación';
    
    // Llenar formulario
    document.getElementById('aplicacionId').value = app.apl_id;
    document.getElementById('aplicacionNombre').value = app.apl_nombre;
    document.getElementById('aplicacionDescripcion').value = app.apl_descripcion || '';
    document.getElementById('aplicacionFechaInicio').value = app.apl_fecha_inicio;
    document.getElementById('aplicacionFechaFin').value = app.apl_fecha_fin || '';
    document.getElementById('aplicacionPorcentajeObjetivo').value = app.apl_porcentaje_objetivo;
    document.getElementById('aplicacionEstado').value = app.apl_estado;
    document.getElementById('aplicacionResponsable').value = app.apl_responsable;
    
    const modal = new Modal(document.getElementById('modalAplicacion'));
    modal.show();
}

// Guardar aplicación
async function guardarAplicacion() {
    const formData = new FormData(document.getElementById('formAplicacion'));
    const btnGuardar = document.getElementById('btnGuardarAplicacion');
    
    // Deshabilitar botón
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Guardando...';
    
    try {
        const url = aplicacionEditando ? '/montoya_proyecto/aplicaciones/modificarAPI' : '/montoya_proyecto/aplicaciones/guardarAPI';
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.codigo === 1) {
            await Toast.fire({
                icon: 'success',
                title: data.mensaje
            });
            
            Modal.getInstance(document.getElementById('modalAplicacion')).hide();
            await cargarAplicaciones();
            await cargarEstadisticas();
        } else {
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: data.mensaje
            });
        }
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            ...swalConfig,
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar la aplicación'
        });
    } finally {
        // Rehabilitar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-floppy"></i> Guardar';
    }
}

// ================================
// FUNCIONES DE ACCIONES
// ================================

// Ver detalle de aplicación
async function verDetalleAplicacion(id) {
    const app = aplicaciones.find(a => a.apl_id == id);
    if (!app) return;
    
    const contenido = `
        <div class="text-start">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Responsable:</strong><br>
                    ${app.responsable_nombre || 'Sin asignar'}
                    ${app.usu_grado ? `<br><small class="text-muted">${app.usu_grado}</small>` : ''}
                </div>
                <div class="col-md-6">
                    <strong>Estado:</strong><br>
                    <span class="badge ${obtenerClaseEstado(app.apl_estado)}">
                        ${formatearEstado(app.apl_estado)}
                    </span>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Fecha Inicio:</strong><br>
                    ${formatearFecha(app.apl_fecha_inicio)}
                </div>
                <div class="col-md-6">
                    <strong>Fecha Fin:</strong><br>
                    ${app.apl_fecha_fin ? formatearFecha(app.apl_fecha_fin) : 'No definida'}
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Progreso Actual:</strong><br>
                    ${app.ultimo_porcentaje}%
                </div>
                <div class="col-md-6">
                    <strong>Meta:</strong><br>
                    ${app.apl_porcentaje_objetivo}%
                </div>
            </div>
            
            ${app.apl_descripcion ? `
            <div class="mb-3">
                <strong>Descripción:</strong><br>
                ${app.apl_descripcion}
            </div>
            ` : ''}
            
            <div class="mb-3">
                <strong>Creado:</strong><br>
                ${formatearFecha(app.apl_creado_en)}
            </div>
        </div>
    `;
    
    await Swal.fire({
        ...swalConfig,
        title: `<i class="bi bi-grid-3x3-gap me-2"></i>${app.apl_nombre}`,
        html: contenido,
        width: 600,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
    });
}

// Cambiar estado de aplicación
async function cambiarEstadoAplicacion(id, estadoActual) {
    const estados = [
        { value: 'EN_PLANIFICACION', text: 'En Planificación' },
        { value: 'EN_PROGRESO', text: 'En Progreso' },
        { value: 'PAUSADO', text: 'Pausado' },
        { value: 'CERRADO', text: 'Cerrado' }
    ];
    
    const opciones = estados.filter(e => e.value !== estadoActual)
        .map(e => `<option value="${e.value}">${e.text}</option>`)
        .join('');
    
    const { value: nuevoEstado } = await Swal.fire({
        ...swalConfig,
        title: 'Cambiar Estado',
        html: `
            <div class="mb-3">
                <label class="form-label">Seleccione el nuevo estado:</label>
                <select id="nuevoEstado" class="form-select">
                    <option value="">Seleccione...</option>
                    ${opciones}
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const estado = document.getElementById('nuevoEstado').value;
            if (!estado) {
                Swal.showValidationMessage('Debe seleccionar un estado');
                return false;
            }
            return estado;
        }
    });
    
    if (nuevoEstado) {
        try {
            const formData = new FormData();
            formData.append('apl_id', id);
            formData.append('apl_estado', nuevoEstado);
            
            const response = await fetch('/montoya_proyecto/aplicaciones/cambiarEstadoAPI', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.codigo === 1) {
                await Toast.fire({
                    icon: 'success',
                    title: data.mensaje
                });
                
                await cargarAplicaciones();
                await cargarEstadisticas();
            } else {
                await Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: 'Error al cambiar el estado'
            });
        }
    }
}

// Eliminar aplicación
async function eliminarAplicacion(id) {
    const app = aplicaciones.find(a => a.apl_id == id);
    if (!app) return;
    
    const result = await Swal.fire({
        ...swalConfig,
        title: '¿Confirmar eliminación?',
        text: `¿Está seguro de eliminar la aplicación "${app.apl_nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: THEME_COLORS.danger
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('apl_id', id);
            
            const response = await fetch('/montoya_proyecto/aplicaciones/eliminarAPI', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.codigo === 1) {
                await Toast.fire({
                    icon: 'success',
                    title: data.mensaje
                });
                
                await cargarAplicaciones();
                await cargarEstadisticas();
            } else {
                await Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: 'Error al eliminar la aplicación'
            });
        }
    }
}

// ================================
// FUNCIONES DE FILTROS
// ================================

// Aplicar filtros
function aplicarFiltros() {
    const filtroEstado = document.getElementById('filtroEstado').value;
    const filtroResponsable = document.getElementById('filtroResponsable').value;
    const buscarTexto = document.getElementById('buscarTexto').value.toLowerCase();
    
    let appsFiltradas = aplicaciones;
    
    // Filtro por estado
    if (filtroEstado) {
        appsFiltradas = appsFiltradas.filter(app => app.apl_estado === filtroEstado);
    }
    
    // Filtro por responsable
    if (filtroResponsable) {
        appsFiltradas = appsFiltradas.filter(app => app.apl_responsable == filtroResponsable);
    }
    
    // Filtro por texto
    if (buscarTexto) {
        appsFiltradas = appsFiltradas.filter(app => 
            app.apl_nombre.toLowerCase().includes(buscarTexto) ||
            (app.apl_descripcion && app.apl_descripcion.toLowerCase().includes(buscarTexto))
        );
    }
    
    mostrarAplicaciones(appsFiltradas);
}

// ================================
// FUNCIONES AUXILIARES
// ================================

// Formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    return dayjs(fecha).format('DD/MM/YYYY');
}

// Obtener clase CSS según el estado
function obtenerClaseEstado(estado) {
    const clases = {
        'EN_PLANIFICACION': 'bg-info',
        'EN_PROGRESO': 'bg-success',
        'PAUSADO': 'bg-warning',
        'CERRADO': 'bg-secondary'
    };
    return clases[estado] || 'bg-secondary';
}

// Formatear estado para mostrar
function formatearEstado(estado) {
    const estados = {
        'EN_PLANIFICACION': 'Planificación',
        'EN_PROGRESO': 'En Progreso',
        'PAUSADO': 'Pausado',
        'CERRADO': 'Cerrado'
    };
    return estados[estado] || estado;
}

// Obtener clase CSS según el porcentaje
function obtenerClaseProgreso(porcentaje) {
    if (porcentaje >= 80) return 'bg-success';
    if (porcentaje >= 50) return 'bg-warning';
    if (porcentaje > 0) return 'bg-danger';
    return 'bg-secondary';
}

// Mostrar error
function mostrarError(mensaje) {
    Toast.fire({
        icon: 'error',
        title: mensaje
    });
}

// Toast para notificaciones rápidas
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: THEME_COLORS.dark,
    color: THEME_COLORS.text,
    customClass: {
        popup: 'swal-dark-theme'
    }
});

// Exponer funciones globales
window.mostrarFormularioNuevaAplicacion = mostrarFormularioNuevaAplicacion;
window.editarAplicacion = editarAplicacion;
window.verDetalleAplicacion = verDetalleAplicacion;
window.cambiarEstadoAplicacion = cambiarEstadoAplicacion;
window.eliminarAplicacion = eliminarAplicacion;
window.aplicarFiltros = aplicarFiltros;
window.cargarEstadisticas = cargarEstadisticas;
window.cargarAplicaciones = cargarAplicaciones;