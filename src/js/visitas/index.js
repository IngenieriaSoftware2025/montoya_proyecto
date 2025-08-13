import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import dayjs from 'dayjs';
import 'dayjs/locale/es';

// Configurar dayjs en español
dayjs.locale('es');

// Paleta de colores para tema cemento/gris - IGUAL AL EJEMPLO ✅
const THEME_COLORS = {
    primary: '#3b82f6',
    success: '#059669',      // Verde esmeralda - igual al ejemplo
    warning: '#d97706',      // Naranja tierra - igual al ejemplo
    danger: '#dc2626',       // Rojo ladrillo - igual al ejemplo
    secondary: '#475569',
    
    // Fondos grises claros
    bgPrimary: '#f8fafc',    // Gris muy claro - base
    bgSecondary: '#f1f5f9',  // Gris claro - cards
    bgTertiary: '#e2e8f0',   // Gris medio - inputs
    
    // Textos
    text: '#1e293b',         // Texto principal oscuro
    textSecondary: '#475569', // Texto secundario
    textMuted: '#64748b',    // Texto atenuado
    
    border: '#d1d5db',
    
    // Colores industriales específicos
    concreteLight: '#f7f8fc',
    concreteMedium: '#e5e7eb',
    concreteDark: '#9ca3af',
    steelBlue: '#64748b',
    industrialOrange: '#ea580c',
    urbanGreen: '#16a34a'
};

// Variables globales
let visitasData = [];
let aplicacionesData = [];
let estadisticasData = {};
let visitaEditando = null;

// URLs de las APIs
const urls = {
    visitas: '/montoya_proyecto/API/visitas/buscar',
    aplicaciones: '/montoya_proyecto/API/visitas/aplicaciones',
    guardar: '/montoya_proyecto/API/visitas/guardar',
    modificar: '/montoya_proyecto/API/visitas/modificar',
    eliminar: '/montoya_proyecto/API/visitas/eliminar',
    estadisticas: '/montoya_proyecto/API/visitas/estadisticas'
};

// Configuración SweetAlert2 con tema cemento
const swalConfig = {
    background: THEME_COLORS.bgPrimary,
    color: THEME_COLORS.text,
    confirmButtonColor: THEME_COLORS.primary,
    cancelButtonColor: THEME_COLORS.secondary,
    customClass: {
        popup: 'swal-cement-theme',
        title: 'swal-cement-title',
        content: 'swal-cement-content',
        confirmButton: 'btn-cement-theme',
        cancelButton: 'btn-cement-theme'
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    aplicarEstilosSwalCemento();
    inicializarModulo();
    configurarEventListeners();
});

// Aplicar estilos cemento a SweetAlert2
const aplicarEstilosSwalCemento = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-cement-theme {
            background: linear-gradient(145deg, ${THEME_COLORS.bgPrimary} 0%, white 100%) !important;
            color: ${THEME_COLORS.text} !important;
            border: 2px solid ${THEME_COLORS.border} !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
            border-radius: 16px !important;
        }
        
        .swal-cement-title {
            color: ${THEME_COLORS.text} !important;
            font-weight: 600 !important;
        }
        
        .swal-cement-content {
            color: ${THEME_COLORS.textSecondary} !important;
        }
        
        .swal2-input, .swal2-textarea, .swal2-select {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 2px solid ${THEME_COLORS.border} !important;
            color: ${THEME_COLORS.text} !important;
            border-radius: 10px !important;
        }
        
        .swal2-input:focus, .swal2-textarea:focus, .swal2-select:focus {
            border-color: ${THEME_COLORS.primary} !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }
        
        .btn-cement-theme {
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
        }
    `;
    document.head.appendChild(style);
};

// Función principal de inicialización
const inicializarModulo = async () => {
    try {
        await Promise.all([
            cargarAplicaciones(),
            cargarVisitas(),
            cargarEstadisticas()
        ]);
        
        Toast.fire({
            icon: 'success',
            title: 'Módulo de visitas cargado correctamente'
        });
    } catch (error) {
        console.error('Error al inicializar módulo:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al cargar el módulo de visitas'
        });
    }
};

// Cargar aplicaciones para los selects
const cargarAplicaciones = async () => {
    try {
        const response = await fetch(urls.aplicaciones);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            aplicacionesData = resultado.data;
            llenarSelectAplicaciones();
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
    }
};

// Llenar selects de aplicaciones
const llenarSelectAplicaciones = () => {
    const selects = ['filtro-aplicacion', 'visita_aplicacion'];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        if (selectId === 'filtro-aplicacion') {
            select.innerHTML = '<option value="">Todas las aplicaciones</option>';
        } else {
            select.innerHTML = '<option value="">Seleccione una aplicación...</option>';
        }
        
        aplicacionesData.forEach(app => {
            const option = document.createElement('option');
            option.value = app.apl_id;
            option.textContent = app.apl_nombre;
            select.appendChild(option);
        });
    });
};

// Cargar visitas
const cargarVisitas = async () => {
    try {
        const response = await fetch(urls.visitas);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            visitasData = resultado.data;
            renderizarVisitas(visitasData);
            actualizarResumen();
        } else {
            document.getElementById('contenedor-visitas').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-circle-fill text-muted" style="font-size: 3rem;"></i>
                    <div class="mt-2 text-muted">${resultado.mensaje}</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar visitas:', error);
        document.getElementById('contenedor-visitas').innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="bi bi-x-circle-fill" style="font-size: 3rem;"></i>
                <div class="mt-2">Error al cargar las visitas</div>
            </div>
        `;
    }
};

// Renderizar visitas
const renderizarVisitas = (visitas) => {
    const contenedor = document.getElementById('contenedor-visitas');
    
    if (visitas.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <div class="mt-2 text-muted">No hay visitas registradas</div>
            </div>
        `;
        return;
    }
    
    const html = visitas.map(visita => {
        const conformidadBadge = visita.vis_conformidad === 't' ? 
            '<span class="badge bg-success">Conforme</span>' : 
            '<span class="badge bg-warning">No Conforme</span>';
        
        const tieneSolucion = visita.vis_solucion && visita.vis_solucion.trim().length > 0;
        const solucionBadge = tieneSolucion ? 
            '<span class="badge bg-info ms-1">Con Solución</span>' : '';
        
        const fechaFormateada = dayjs(visita.vis_fecha).format('DD/MM/YYYY HH:mm');
        const esHoy = dayjs(visita.vis_fecha).isSame(dayjs(), 'day');
        
        return `
            <div class="card card-visita mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="card-title mb-1">${visita.apl_nombre}</h6>
                            <small class="text-muted">
                                <i class="bi bi-calendar-event me-1"></i>
                                ${fechaFormateada}
                                ${esHoy ? '<span class="badge bg-primary ms-1">Hoy</span>' : ''}
                            </small>
                        </div>
                        <div>
                            ${conformidadBadge}
                            ${solucionBadge}
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Visitante:</strong> ${visita.vis_quien}
                    </div>
                    
                    <div class="mb-2">
                        <strong>Motivo:</strong> ${visita.vis_motivo}
                    </div>
                    
                    ${visita.vis_procedimiento ? `
                        <div class="mb-2">
                            <strong>Procedimiento:</strong> ${visita.vis_procedimiento}
                        </div>
                    ` : ''}
                    
                    ${visita.vis_solucion ? `
                        <div class="mb-2">
                            <strong>Solución:</strong> ${visita.vis_solucion}
                        </div>
                    ` : ''}
                    
                    ${visita.vis_observacion ? `
                        <div class="mb-2">
                            <strong>Observaciones:</strong> ${visita.vis_observacion}
                        </div>
                    ` : ''}
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Registrado por: ${visita.creado_por_nombre || 'Sistema'} 
                            ${visita.usu_grado ? `(${visita.usu_grado})` : ''}
                        </small>
                    </div>
                    
                    <div class="mt-2">
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalleVisita(${visita.vis_id})" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </button>
                            ${esHoy ? `
                                <button class="btn btn-sm btn-outline-warning" onclick="editarVisita(${visita.vis_id})" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarVisita(${visita.vis_id})" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    contenedor.innerHTML = html;
};

// Cargar estadísticas
const cargarEstadisticas = async () => {
    try {
        const response = await fetch(urls.estadisticas);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            estadisticasData = resultado.data;
            actualizarEstadisticas();
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
};

// Actualizar estadísticas en la interfaz
const actualizarEstadisticas = () => {
    if (!estadisticasData.estadisticas) return;
    
    const stats = estadisticasData.estadisticas;
    
    // Actualizar contadores principales
    document.getElementById('total-visitas').textContent = stats.total_visitas || 0;
    document.getElementById('visitas-conformes').textContent = stats.visitas_conformes || 0;
    document.getElementById('visitas-no-conformes').textContent = stats.visitas_no_conformes || 0;
    
    // Calcular visitas del mes actual
    const visitasDelMes = visitasData.filter(visita => 
        dayjs(visita.vis_fecha).isSame(dayjs(), 'month')
    ).length;
    document.getElementById('visitas-mes').textContent = visitasDelMes;
    
    // Calcular tasa de conformidad
    const totalVisitas = stats.total_visitas || 0;
    const conformes = stats.visitas_conformes || 0;
    const tasaConformidad = totalVisitas > 0 ? ((conformes / totalVisitas) * 100) : 0;
    
    document.getElementById('tasa-conformidad').textContent = `${tasaConformidad.toFixed(1)}%`;
    document.getElementById('barra-conformidad').style.width = `${tasaConformidad}%`;
    
    // Actualizar estadísticas del panel lateral
    document.getElementById('stat-conformes').textContent = conformes;
    document.getElementById('stat-no-conformes').textContent = stats.visitas_no_conformes || 0;
};

// Actualizar resumen rápido
const actualizarResumen = () => {
    const totalVisitas = visitasData.length;
    const conformes = visitasData.filter(v => v.vis_conformidad === 't').length;
    const noConformes = visitasData.filter(v => v.vis_conformidad === 'f').length;
    const visitasDelMes = visitasData.filter(v => 
        dayjs(v.vis_fecha).isSame(dayjs(), 'month')
    ).length;
    
    document.getElementById('total-visitas').textContent = totalVisitas;
    document.getElementById('visitas-conformes').textContent = conformes;
    document.getElementById('visitas-no-conformes').textContent = noConformes;
    document.getElementById('visitas-mes').textContent = visitasDelMes;
};

// Configurar event listeners
const configurarEventListeners = () => {
    // Formulario de visita
    document.getElementById('formVisita').addEventListener('submit', async (e) => {
        e.preventDefault();
        await guardarVisita();
    });
};

// Abrir modal para nueva visita
const abrirModalNuevaVisita = () => {
    visitaEditando = null;
    document.getElementById('modalVisitaLabel').innerHTML = '<i class="bi bi-people-fill me-2"></i>Registrar Visita';
    document.getElementById('formVisita').reset();
    document.getElementById('visita_id').value = '';
    document.getElementById('visita_fecha').value = dayjs().format('YYYY-MM-DD');
    document.getElementById('visita_hora').value = dayjs().format('HH:mm');
    
    const modal = new Modal(document.getElementById('modalVisita'));
    modal.show();
};

// Editar visita
const editarVisita = (id) => {
    const visita = visitasData.find(v => v.vis_id == id);
    if (!visita) return;
    
    visitaEditando = visita;
    document.getElementById('modalVisitaLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar Visita';
    
    // Llenar formulario
    document.getElementById('visita_id').value = visita.vis_id;
    document.getElementById('visita_aplicacion').value = visita.vis_apl_id;
    document.getElementById('visita_fecha').value = dayjs(visita.vis_fecha).format('YYYY-MM-DD');
    document.getElementById('visita_hora').value = dayjs(visita.vis_fecha).format('HH:mm');
    document.getElementById('visita_quien').value = visita.vis_quien;
    document.getElementById('visita_motivo').value = visita.vis_motivo;
    document.getElementById('visita_procedimiento').value = visita.vis_procedimiento;
    document.getElementById('visita_solucion').value = visita.vis_solucion || '';
    document.getElementById('visita_observacion').value = visita.vis_observacion || '';
    
    // Seleccionar conformidad
    if (visita.vis_conformidad === 't') {
        document.getElementById('conformidad_si').checked = true;
    } else {
        document.getElementById('conformidad_no').checked = true;
    }
    
    const modal = new Modal(document.getElementById('modalVisita'));
    modal.show();
};

// Guardar visita
const guardarVisita = async () => {
    const btnGuardar = document.getElementById('btnGuardarVisita');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i>Guardando...';
    
    try {
        const formData = new FormData(document.getElementById('formVisita'));
        const url = visitaEditando ? urls.modificar : urls.guardar;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            await Swal.fire({
                ...swalConfig,
                icon: 'success',
                title: 'Visita guardada',
                text: resultado.mensaje,
                showConfirmButton: true
            });
            
            Modal.getInstance(document.getElementById('modalVisita')).hide();
            await cargarVisitas();
            await cargarEstadisticas();
        } else {
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: resultado.mensaje,
                showConfirmButton: true
            });
        }
    } catch (error) {
        console.error('Error al guardar visita:', error);
        await Swal.fire({
            ...swalConfig,
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar la visita',
            showConfirmButton: true
        });
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-floppy-fill me-1"></i>Guardar Visita';
    }
};

// Ver detalle de visita
const verDetalleVisita = async (id) => {
    const visita = visitasData.find(v => v.vis_id == id);
    if (!visita) return;
    
    const fechaFormateada = dayjs(visita.vis_fecha).format('dddd, DD [de] MMMM [de] YYYY [a las] HH:mm');
    const conformidad = visita.vis_conformidad === 't' ? 'Conforme' : 'No Conforme';
    const conformidadColor = visita.vis_conformidad === 't' ? 'success' : 'warning';
    
    const contenido = `
        <div class="text-start">
            <div class="mb-3">
                <strong>Aplicación:</strong><br>
                ${visita.apl_nombre}
            </div>
            
            <div class="mb-3">
                <strong>Fecha y Hora:</strong><br>
                ${fechaFormateada}
            </div>
            
            <div class="mb-3">
                <strong>Visitante:</strong><br>
                ${visita.vis_quien}
            </div>
            
            <div class="mb-3">
                <strong>Motivo:</strong><br>
                ${visita.vis_motivo}
            </div>
            
            <div class="mb-3">
                <strong>Procedimiento:</strong><br>
                ${visita.vis_procedimiento}
            </div>
            
            ${visita.vis_solucion ? `
                <div class="mb-3">
                    <strong>Solución:</strong><br>
                    ${visita.vis_solucion}
                </div>
            ` : ''}
            
            ${visita.vis_observacion ? `
                <div class="mb-3">
                    <strong>Observaciones:</strong><br>
                    ${visita.vis_observacion}
                </div>
            ` : ''}
            
            <div class="mb-3">
                <strong>Conformidad:</strong><br>
                <span class="badge bg-${conformidadColor}">${conformidad}</span>
            </div>
            
            <div class="mb-3">
                <strong>Registrado por:</strong><br>
                ${visita.creado_por_nombre || 'Sistema'} ${visita.usu_grado ? `(${visita.usu_grado})` : ''}
            </div>
        </div>
    `;
    
    await Swal.fire({
        ...swalConfig,
        title: `<i class="bi bi-people-fill me-2"></i>Detalle de Visita`,
        html: contenido,
        width: 600,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
    });
};

// Eliminar visita
const eliminarVisita = async (id) => {
    const visita = visitasData.find(v => v.vis_id == id);
    if (!visita) return;
    
    const result = await Swal.fire({
        ...swalConfig,
        title: '¿Confirmar eliminación?',
        text: `¿Está seguro de eliminar esta visita de ${visita.vis_quien}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: THEME_COLORS.danger
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('vis_id', id);
            
            const response = await fetch(urls.eliminar, {
                method: 'POST',
                body: formData
            });
            
            const resultado = await response.json();
            
            if (resultado.codigo === 1) {
                await Toast.fire({
                    icon: 'success',
                    title: resultado.mensaje
                });
                
                await cargarVisitas();
                await cargarEstadisticas();
            } else {
                await Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: 'Error',
                    text: resultado.mensaje
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: 'Error al eliminar la visita'
            });
        }
    }
};

// Aplicar filtros
const aplicarFiltros = () => {
    const filtroApp = document.getElementById('filtro-aplicacion').value;
    const filtroConformidad = document.getElementById('filtro-conformidad').value;
    const filtroFechaInicio = document.getElementById('filtro-fecha-inicio').value;
    const filtroFechaFin = document.getElementById('filtro-fecha-fin').value;
    
    let visitasFiltradas = visitasData;
    
    // Filtro por aplicación
    if (filtroApp) {
        visitasFiltradas = visitasFiltradas.filter(v => v.vis_apl_id == filtroApp);
    }
    
    // Filtro por conformidad
    if (filtroConformidad !== '') {
        const conformidadBuscada = filtroConformidad === 'true' ? 't' : 'f';
        visitasFiltradas = visitasFiltradas.filter(v => v.vis_conformidad === conformidadBuscada);
    }
    
    // Filtro por fecha inicio
    if (filtroFechaInicio) {
        visitasFiltradas = visitasFiltradas.filter(v => 
            dayjs(v.vis_fecha).isAfter(dayjs(filtroFechaInicio).subtract(1, 'day'))
        );
    }
    
    // Filtro por fecha fin
    if (filtroFechaFin) {
        visitasFiltradas = visitasFiltradas.filter(v => 
            dayjs(v.vis_fecha).isBefore(dayjs(filtroFechaFin).add(1, 'day'))
        );
    }
    
    renderizarVisitas(visitasFiltradas);
};

// Ver estadísticas detalladas
const verEstadisticasDetalladas = async () => {
    if (!estadisticasData.estadisticas) {
        Toast.fire({
            icon: 'info',
            title: 'No hay estadísticas disponibles'
        });
        return;
    }
    
    const stats = estadisticasData.estadisticas;
    const totalVisitas = stats.total_visitas || 0;
    const conformes = stats.visitas_conformes || 0;
    const noConformes = stats.visitas_no_conformes || 0;
    const conSolucion = stats.visitas_con_solucion || 0;
    
    const tasaConformidad = totalVisitas > 0 ? ((conformes / totalVisitas) * 100).toFixed(1) : 0;
    const tasaSolucion = totalVisitas > 0 ? ((conSolucion / totalVisitas) * 100).toFixed(1) : 0;
    
    const contenido = `
        <div class="text-start">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">${totalVisitas}</h3>
                            <div class="text-muted">Total Visitas</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-success">${tasaConformidad}%</h3>
                            <div class="text-muted">Tasa Conformidad</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-success">${conformes}</h4>
                        <div class="text-muted">Conformes</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-warning">${noConformes}</h4>
                        <div class="text-muted">No Conformes</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-info">${conSolucion}</h4>
                        <div class="text-muted">Con Solución</div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <strong>Período:</strong><br>
                ${stats.primera_fecha ? dayjs(stats.primera_fecha).format('DD/MM/YYYY') : 'N/A'} - 
                ${stats.ultima_fecha ? dayjs(stats.ultima_fecha).format('DD/MM/YYYY') : 'N/A'}
            </div>
            
            <div class="mb-3">
                <strong>Tasa de Solución:</strong><br>
                <div class="progress">
                    <div class="progress-bar bg-info" style="width: ${tasaSolucion}%">${tasaSolucion}%</div>
                </div>
            </div>
        </div>
    `;
    
    await Swal.fire({
        ...swalConfig,
        title: '<i class="bi bi-bar-chart-fill me-2"></i>Estadísticas Detalladas',
        html: contenido,
        width: 700,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
    });
};

// Ver visitas no conformes
const verVisitasNoConformes = () => {
    const noConformes = visitasData.filter(v => v.vis_conformidad === 'f');
    
    if (noConformes.length === 0) {
        Toast.fire({
            icon: 'success',
            title: '¡Excelente! No hay visitas no conformes'
        });
        return;
    }
    
    // Aplicar filtro y mostrar solo las no conformes
    document.getElementById('filtro-conformidad').value = 'false';
    aplicarFiltros();
    
    Toast.fire({
        icon: 'info',
        title: `Mostrando ${noConformes.length} visitas no conformes`
    });
};

// Toast para notificaciones rápidas con tema cemento
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: 'white',
    color: THEME_COLORS.text,
    customClass: {
        popup: 'swal-cement-theme'
    }
});

// Exponer funciones globales
window.abrirModalNuevaVisita = abrirModalNuevaVisita;
window.editarVisita = editarVisita;
window.verDetalleVisita = verDetalleVisita;
window.eliminarVisita = eliminarVisita;
window.aplicarFiltros = aplicarFiltros;
window.verEstadisticasDetalladas = verEstadisticasDetalladas;
window.verVisitasNoConformes = verVisitasNoConformes;
window.cargarVisitas = cargarVisitas;