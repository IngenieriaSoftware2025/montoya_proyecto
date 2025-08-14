// Dashboard Ejecutivo para Gerentes y Subgerentes
// Versión con Bootstrap Icons y estilos CSS corregidos

import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import dayjs from 'dayjs';
import isoWeek from 'dayjs/plugin/isoWeek';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/es';

// Configurar dayjs
dayjs.extend(isoWeek);
dayjs.extend(relativeTime);
dayjs.locale('es');

// Paleta de colores, DASHBOARD DE GERENTE
const COLORS = {
    primary: '#3b82f6',
    success: '#059669',
    warning: '#d97706',
    danger: '#dc2626',
    info: '#0891b2',
    secondary: '#64748b',
    light: '#f8fafc',
    dark: '#1e293b',
    
    // Gradientes para gráficos
    gradients: {
        primary: ['#3b82f6', '#2563eb'],
        success: ['#059669', '#16a34a'],
        warning: ['#d97706', '#ea580c'],
        danger: ['#dc2626', '#b91c1c'],
        info: ['#0891b2', '#0e7490']
    }
};

// URLs de las APIs
const API_URLS = {
    resumenEjecutivo: '/montoya_proyecto/API/gerente/resumen',
    aplicacionesCompletas: '/montoya_proyecto/API/gerente/aplicaciones',
    datosGraficos: '/montoya_proyecto/API/gerente/graficos',
    alertas: '/montoya_proyecto/API/gerente/alertas',
    metricas: '/montoya_proyecto/API/gerente/metricas'
};

// Variables globales
let aplicacionesData = [];
let chartProgreso = null;
let chartEstados = null;
let chartDesarrolladores = null;
let tablaAplicaciones = null;
let intervaloActualizacion = null;
let Chart = null; // Variable para Chart.js cuando se cargue

// Configuración SweetAlert2
const swalConfig = {
    background: COLORS.light,
    color: COLORS.dark,
    confirmButtonColor: COLORS.primary,
    cancelButtonColor: COLORS.secondary,
    customClass: {
        popup: 'executive-modal',
        title: 'executive-modal-title',
        content: 'executive-modal-content'
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    inicializarDashboard();
    configurarEventos();
    iniciarActualizacionAutomatica();
});

/**
 * Inicialización del dashboard
 */
const inicializarDashboard = async () => {
    try {
        mostrarCargando(true);
        
        // Aplicar estilos para SweetAlert2
        aplicarEstilosSwal();
        
        // Cargar datos en paralelo (sin gráficos primero)
        await Promise.all([
            cargarResumenEjecutivo(),
            cargarAplicacionesCompletas(),
            cargarAlertas(),
            cargarMetricas()
        ]);
        
        // Intentar cargar gráficos por separado
        await cargarDatosGraficos();
        
        // Inicializar tabla (sin dependencias externas)
        inicializarTablaSimple();
        
        Toast.fire({
            icon: 'success',
            title: 'Dashboard cargado correctamente'
        });
        
    } catch (error) {
        console.error('Error al inicializar dashboard:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al cargar el dashboard'
        });
    } finally {
        mostrarCargando(false);
    }
};

/**
 * Aplicar estilos para SweetAlert2
 */
const aplicarEstilosSwal = () => {
    const style = document.createElement('style');
    style.textContent = `
        .executive-modal {
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }
        
        .executive-modal-title {
            font-weight: 600 !important;
        }
        
        .executive-modal-content {
            color: ${COLORS.secondary} !important;
        }
        
        .swal2-input, .swal2-textarea, .swal2-select {
            border: 2px solid #d1d5db !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }
        
        .swal2-input:focus, .swal2-textarea:focus, .swal2-select:focus {
            border-color: ${COLORS.primary} !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }
    `;
    document.head.appendChild(style);
};

/**
 * Cargar resumen ejecutivo
 */
const cargarResumenEjecutivo = async () => {
    try {
        const response = await fetch(API_URLS.resumenEjecutivo);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            const data = resultado.data;
            
            // Actualizar métricas principales
            actualizarElementoSeguro('total-aplicaciones', data.total_aplicaciones);
            
            const estadoProgreso = data.estados_aplicaciones?.find(e => e.apl_estado === 'EN_PROGRESO');
            actualizarElementoSeguro('aplicaciones-activas', `${estadoProgreso?.cantidad || 0} activas`);
            
            // Actualizar timestamp
            actualizarElementoSeguro('ultima-actualizacion', `Actualizado: ${data.fecha_actualizacion}`);
            
            // Mostrar alertas críticas si existen
            if (data.aplicaciones_sin_reporte > 0 || data.visitas_no_conformes > 0) {
                mostrarAlertasCriticas(data);
            }
        }
    } catch (error) {
        console.error('Error al cargar resumen ejecutivo:', error);
        mostrarErrorEnElemento('total-aplicaciones', 'Error');
    }
};

/**
 * Función segura para actualizar elementos del DOM
 */
const actualizarElementoSeguro = (id, contenido) => {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = contenido;
    }
};

/**
 * Mostrar error en elemento específico
 */
const mostrarErrorEnElemento = (id, mensaje) => {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = mensaje;
        elemento.classList.add('text-danger');
    }
};

/**
 * Cargar aplicaciones completas
 */
const cargarAplicacionesCompletas = async () => {
    try {
        const response = await fetch(API_URLS.aplicacionesCompletas);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            aplicacionesData = resultado.data || [];
            actualizarTablaAplicaciones();
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
        mostrarErrorTabla('Error al cargar las aplicaciones');
    }
};

/**
 * Mostrar error en tabla
 */
const mostrarErrorTabla = (mensaje) => {
    const tbody = document.getElementById('tablaAplicacionesBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fa-2x mb-2"></i><br>
                    ${mensaje}
                </td>
            </tr>
        `;
    }
};

/**
 * Cargar datos para gráficos con manejo robusto de errores
 */
const cargarDatosGraficos = async () => {
    try {
        const response = await fetch(API_URLS.datosGraficos);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            const data = resultado.data;
            
            // Intentar cargar Chart.js de forma segura
            try {
                // Intentar importar Chart.js
                const ChartModule = await import('chart.js/auto');
                Chart = ChartModule.default;
                
                // Verificar que Chart se cargó correctamente
                if (Chart && typeof Chart === 'function') {
                    await crearGraficos(data);
                } else {
                    throw new Error('Chart.js no se cargó correctamente');
                }
            } catch (chartError) {
                console.error('Error al cargar Chart.js:', chartError);
                mostrarGraficosAlternativos(data);
            }
        }
    } catch (error) {
        console.error('Error al cargar datos de gráficos:', error);
        mostrarErrorGraficos();
    }
};

/**
 * Crear gráficos con Chart.js
 */
const crearGraficos = async (data) => {
    try {
        await Promise.all([
            actualizarGraficoProgreso(data.progreso_temporal),
            actualizarGraficoEstados(data.distribucion_estados),
            actualizarGraficoDesarrolladores(data.velocidad_desarrolladores)
        ]);
    } catch (error) {
        console.error('Error al crear gráficos:', error);
        mostrarGraficosAlternativos(data);
    }
};

/**
 * Mostrar gráficos alternativos cuando Chart.js falla
 */
const mostrarGraficosAlternativos = (data) => {
    // Gráfico de progreso alternativo
    mostrarProgresoAlternativo(data.progreso_temporal);
    
    // Gráfico de estados alternativo
    mostrarEstadosAlternativo(data.distribucion_estados);
    
    // Gráfico de desarrolladores alternativo
    mostrarDesarrolladoresAlternativo(data.velocidad_desarrolladores);
};

/**
 * Gráfico de desarrolladores alternativo
 */
const mostrarDesarrolladoresAlternativo = (datosDesarrolladores) => {
    const container = document.getElementById('chartDesarrolladores')?.parentElement;
    if (!container) return;
    
    if (!datosDesarrolladores || datosDesarrolladores.length === 0) {
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                <div class="text-center">
                    <i class="bi bi-people fa-3x mb-3"></i>
                    <div>No hay datos de desarrolladores disponibles</div>
                </div>
            </div>
        `;
        return;
    }
    
    const top5 = datosDesarrolladores
        .filter(dev => dev.velocidad_semanal !== null)
        .sort((a, b) => parseFloat(b.velocidad_semanal) - parseFloat(a.velocidad_semanal))
        .slice(0, 5);
    
    const maxVelocidad = Math.max(...top5.map(dev => parseFloat(dev.velocidad_semanal) || 0));
    
    let html = '<div class="alternative-chart">';
    html += '<h6 class="mb-3">Top Desarrolladores</h6>';
    
    top5.forEach(dev => {
        const velocidad = parseFloat(dev.velocidad_semanal) || 0;
        const porcentaje = maxVelocidad > 0 ? (velocidad / maxVelocidad) * 100 : 0;
        const nombre = dev.usu_nombre.split(' ')[0];
        
        html += `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-bold">${nombre}</small>
                    <small>${velocidad} pts/semana</small>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" style="width: ${porcentaje}%; background-color: ${COLORS.primary};"></div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
};

/**
 * Crear fila de aplicación - CORREGIDA CON BOOTSTRAP ICONS
 */
const crearFilaAplicacion = (app) => {
    const row = document.createElement('tr');
    row.className = `semaforo-${app.semaforo || 'verde'}`;
    
    const velocidadColor = (app.velocidad_semanal || 0) > 5 ? 'success' : 
                          (app.velocidad_semanal || 0) > 0 ? 'warning' : 'danger';
    const diasTexto = app.dias_sin_reporte === 999 ? 'Nunca' : 
                     app.dias_sin_reporte === 0 ? 'Hoy' : 
                     `${Math.floor(app.dias_sin_reporte || 0)} días`;
    
    row.innerHTML = `
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="fw-bold">${app.apl_nombre || 'Sin nombre'}</div>
                    <small class="text-muted">${app.apl_descripcion || 'Sin descripción'}</small>
                </div>
            </div>
        </td>
        <td class="text-center">
            <div class="fw-bold">${app.responsable_nombre || 'Sin asignar'}</div>
            <small class="text-muted">${app.usu_grado || ''}</small>
        </td>
        <td class="text-center">
            <span class="executive-badge ${obtenerClaseEstado(app.apl_estado)}">
                ${formatearEstado(app.apl_estado)}
            </span>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="executive-progress flex-grow-1 me-2">
                    <div class="progress-bar" style="width: ${app.ultimo_porcentaje || 0}%"></div>
                </div>
                <small class="fw-bold">${app.ultimo_porcentaje || 0}%</small>
            </div>
        </td>
        <td class="text-center">
            <span class="executive-badge ${velocidadColor}">
                ${(app.velocidad_semanal || 0) > 0 ? '+' : ''}${app.velocidad_semanal || 0}
            </span>
        </td>
        <td class="text-center">
            <small class="${(app.dias_sin_reporte || 0) > 2 ? 'text-danger' : (app.dias_sin_reporte || 0) > 0 ? 'text-warning' : 'text-success'}">
                ${diasTexto}
            </small>
        </td>
        <td class="text-center">
            <i class="bi bi-circle-fill text-${(app.semaforo || 'verde') === 'rojo' ? 'danger' : (app.semaforo || 'verde') === 'ambar' ? 'warning' : 'success'}" 
               title="${obtenerDescripcionSemaforo(app.semaforo || 'verde')}"></i>
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-primary" onclick="verDetalleAplicacion(${app.apl_id})" 
                        title="Ver detalles">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-outline-warning" onclick="abrirAccionesRapidas(${app.apl_id})" 
                        title="Acciones rápidas">
                    <i class="bi bi-lightning"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
};

/**
 * Variables para tabla simple
 */
let paginaActual = 1;
let filasPorPagina = 25;
let datosOriginales = [];
let datosFiltrados = [];

/**
 * Inicializar tabla simple (sin DataTables)
 */
const inicializarTablaSimple = () => {
    const tabla = document.getElementById('tablaAplicaciones');
    if (!tabla) return;
    
    // Agregar clases para funcionalidad básica
    tabla.classList.add('table-sortable');
    
    // Agregar filtro de búsqueda
    const container = tabla.parentElement;
    const filtroHTML = `
        <div class="table-filter">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" id="filtroTabla" class="form-control" placeholder="Buscar aplicaciones...">
                </div>
                <div class="col-md-6">
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="EN_PLANIFICACION">Planificación</option>
                        <option value="EN_PROGRESO">En Progreso</option>
                        <option value="PAUSADO">Pausado</option>
                        <option value="CERRADO">Cerrado</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', filtroHTML);
    
    // Configurar eventos de filtro
    document.getElementById('filtroTabla')?.addEventListener('input', filtrarTabla);
    document.getElementById('filtroEstado')?.addEventListener('change', filtrarTabla);
    
    // Agregar paginación
    const paginacionHTML = `
        <div class="table-pagination">
            <div class="pagination-info" id="paginacionInfo"></div>
            <div class="pagination-controls" id="paginacionControles"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', paginacionHTML);
    
    // Configurar ordenamiento
    configurarOrdenamiento();
};

/**
 * Actualizar tabla de aplicaciones
 */
const actualizarTablaAplicaciones = () => {
    datosOriginales = [...aplicacionesData];
    datosFiltrados = [...aplicacionesData];
    
    renderizarTabla();
    actualizarPaginacion();
};

/**
 * Renderizar tabla
 */
const renderizarTabla = () => {
    const tbody = document.getElementById('tablaAplicacionesBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (datosFiltrados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-search fa-2x mb-2"></i><br>
                    No se encontraron aplicaciones
                </td>
            </tr>
        `;
        return;
    }
    
    const inicio = (paginaActual - 1) * filasPorPagina;
    const fin = inicio + filasPorPagina;
    const datosAPaginar = datosFiltrados.slice(inicio, fin);
    
    datosAPaginar.forEach(app => {
        const row = crearFilaAplicacion(app);
        tbody.appendChild(row);
    });
};

/**
 * Filtrar tabla
 */
const filtrarTabla = () => {
    const textoBusqueda = document.getElementById('filtroTabla')?.value.toLowerCase() || '';
    const estadoFiltro = document.getElementById('filtroEstado')?.value || '';
    
    datosFiltrados = datosOriginales.filter(app => {
        const coincideTexto = !textoBusqueda || 
            (app.apl_nombre || '').toLowerCase().includes(textoBusqueda) ||
            (app.responsable_nombre || '').toLowerCase().includes(textoBusqueda) ||
            (app.apl_descripcion || '').toLowerCase().includes(textoBusqueda);
        
        const coincideEstado = !estadoFiltro || app.apl_estado === estadoFiltro;
        
        return coincideTexto && coincideEstado;
    });
    
    paginaActual = 1;
    renderizarTabla();
    actualizarPaginacion();
};

/**
 * Configurar ordenamiento
 */
const configurarOrdenamiento = () => {
    const headers = document.querySelectorAll('#tablaAplicaciones thead th');
    
    headers.forEach((header, index) => {
        if (index === 7) return; // Skip actions column
        
        header.addEventListener('click', () => {
            const orden = header.classList.contains('sorted-asc') ? 'desc' : 'asc';
            
            // Remove all sorting classes
            headers.forEach(h => h.classList.remove('sorted-asc', 'sorted-desc'));
            
            // Add new sorting class
            header.classList.add(orden === 'asc' ? 'sorted-asc' : 'sorted-desc');
            
            // Sort data
            ordenarDatos(index, orden);
        });
    });
};

/**
 * Ordenar datos
 */
const ordenarDatos = (columna, orden) => {
    datosFiltrados.sort((a, b) => {
        let valorA, valorB;
        
        switch (columna) {
            case 0: // Nombre
                valorA = a.apl_nombre || '';
                valorB = b.apl_nombre || '';
                break;
            case 1: // Responsable
                valorA = a.responsable_nombre || '';
                valorB = b.responsable_nombre || '';
                break;
            case 2: // Estado
                valorA = a.apl_estado || '';
                valorB = b.apl_estado || '';
                break;
            case 3: // Progreso
                valorA = a.ultimo_porcentaje || 0;
                valorB = b.ultimo_porcentaje || 0;
                break;
            case 4: // Velocidad
                valorA = a.velocidad_semanal || 0;
                valorB = b.velocidad_semanal || 0;
                break;
            case 5: // Días sin reporte
                valorA = a.dias_sin_reporte || 0;
                valorB = b.dias_sin_reporte || 0;
                break;
            default:
                return 0;
        }
        
        if (typeof valorA === 'string') {
            return orden === 'asc' ? 
                valorA.localeCompare(valorB) : 
                valorB.localeCompare(valorA);
        } else {
            return orden === 'asc' ? 
                valorA - valorB : 
                valorB - valorA;
        }
    });
    
    paginaActual = 1;
    renderizarTabla();
    actualizarPaginacion();
};

/**
 * Actualizar paginación
 */
const actualizarPaginacion = () => {
    const totalPaginas = Math.ceil(datosFiltrados.length / filasPorPagina);
    const inicio = (paginaActual - 1) * filasPorPagina + 1;
    const fin = Math.min(paginaActual * filasPorPagina, datosFiltrados.length);
    
    // Actualizar información
    const infoElement = document.getElementById('paginacionInfo');
    if (infoElement) {
        infoElement.textContent = `Mostrando ${inicio} a ${fin} de ${datosFiltrados.length} registros`;
    }
    
    // Actualizar controles
    const controlesElement = document.getElementById('paginacionControles');
    if (controlesElement) {
        let html = '';
        
        // Botón anterior
        html += `<button onclick="cambiarPagina(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}>Anterior</button>`;
        
        // Números de página
        const maxBotones = 5;
        let inicio = Math.max(1, paginaActual - Math.floor(maxBotones / 2));
        let fin = Math.min(totalPaginas, inicio + maxBotones - 1);
        
        if (fin - inicio < maxBotones - 1) {
            inicio = Math.max(1, fin - maxBotones + 1);
        }
        
        for (let i = inicio; i <= fin; i++) {
            html += `<button onclick="cambiarPagina(${i})" ${i === paginaActual ? 'class="active"' : ''}>${i}</button>`;
        }
        
        // Botón siguiente
        html += `<button onclick="cambiarPagina(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}>Siguiente</button>`;
        
        controlesElement.innerHTML = html;
    }
};

/**
 * Cambiar página
 */
const cambiarPagina = (nuevaPagina) => {
    const totalPaginas = Math.ceil(datosFiltrados.length / filasPorPagina);
    
    if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
        paginaActual = nuevaPagina;
        renderizarTabla();
        actualizarPaginacion();
    }
};

/**
 * Cargar alertas
 */
const cargarAlertas = async () => {
    try {
        const response = await fetch(API_URLS.alertas);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            mostrarActividadReciente(resultado.data);
        }
    } catch (error) {
        console.error('Error al cargar alertas:', error);
        mostrarActividadRecienteError();
    }
};

/**
 * Cargar métricas de rendimiento
 */
const cargarMetricas = async () => {
    try {
        const response = await fetch(API_URLS.metricas);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            const data = resultado.data;
            
            actualizarElementoSeguro('cumplimiento-reportes', `${data.cumplimiento_reportes}%`);
            actualizarElementoSeguro('velocidad-promedio', data.velocidad_promedio);
            actualizarElementoSeguro('aplicaciones-riesgo', contarAplicacionesRiesgo());
            
            actualizarColoresMetricas(data);
        }
    } catch (error) {
        console.error('Error al cargar métricas:', error);
        mostrarErrorEnElemento('cumplimiento-reportes', 'Error');
        mostrarErrorEnElemento('velocidad-promedio', 'Error');
        mostrarErrorEnElemento('aplicaciones-riesgo', 'Error');
    }
};

/**
 * Funciones auxiliares
 */
const mostrarAlertasCriticas = (data) => {
    const contenedor = document.getElementById('alertas-criticas');
    if (!contenedor) return;
    
    let alertas = [];
    
    if (data.aplicaciones_sin_reporte > 0) {
        alertas.push(`
            <div class="executive-alert danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>¡Atención!</strong> ${data.aplicaciones_sin_reporte} aplicación(es) sin reporte hoy.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }
    
    if (data.visitas_no_conformes > 0) {
        alertas.push(`
            <div class="executive-alert warning alert-dismissible fade show" role="alert">
                <i class="bi bi-person-x me-2"></i>
                <strong>Pendiente:</strong> ${data.visitas_no_conformes} visita(s) no conforme(s) requieren atención.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }
    
    contenedor.innerHTML = alertas.join('');
};

const mostrarActividadReciente = (alertas) => {
    const contenedor = document.getElementById('actividad-reciente');
    if (!contenedor) return;
    
    if (!alertas || alertas.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="bi bi-check-circle fa-2x mb-2"></i>
                <div>Todo bajo control</div>
            </div>
        `;
        return;
    }
    
    const html = alertas.map(alerta => `
        <div class="d-flex align-items-start mb-3 p-2 rounded bg-light">
            <div class="flex-shrink-0 me-2">
                <i class="${alerta.icono || 'bi bi-info-circle'} text-${alerta.tipo || 'info'}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold small">${alerta.titulo || 'Sin título'}</div>
                <div class="small text-muted">${alerta.mensaje || 'Sin mensaje'}</div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    ${alerta.timestamp ? dayjs(alerta.timestamp).fromNow() : 'Hace un momento'}
                </div>
            </div>
        </div>
    `).join('');
    
    contenedor.innerHTML = html;
};

const mostrarActividadRecienteError = () => {
    const contenedor = document.getElementById('actividad-reciente');
    if (contenedor) {
        contenedor.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="bi bi-exclamation-triangle fa-2x mb-2"></i>
                <div>Error al cargar actividad</div>
            </div>
        `;
    }
};

const configurarEventos = () => {
    // Botones de período en gráfico de progreso
    document.querySelectorAll('[data-period]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
        });
    });
};

const obtenerClaseEstado = (estado) => {
    const clases = {
        'EN_PLANIFICACION': 'info',
        'EN_PROGRESO': 'success',
        'PAUSADO': 'warning',
        'CERRADO': 'secondary'
    };
    return clases[estado] || 'secondary';
};

const formatearEstado = (estado) => {
    const nombres = {
        'EN_PLANIFICACION': 'Planificación',
        'EN_PROGRESO': 'En Progreso',
        'PAUSADO': 'Pausado',
        'CERRADO': 'Cerrado'
    };
    return nombres[estado] || estado;
};

const obtenerDescripcionSemaforo = (semaforo) => {
    const descripciones = {
        'verde': 'Todo en orden',
        'ambar': 'Requiere atención',
        'rojo': 'Situación crítica'
    };
    return descripciones[semaforo] || '';
};

const contarAplicacionesRiesgo = () => {
    return aplicacionesData.filter(app => 
        (app.semaforo === 'rojo') || 
        (app.semaforo === 'ambar' && (app.dias_sin_reporte || 0) > 1)
    ).length;
};

const actualizarColoresMetricas = (data) => {
    const cumplimiento = data.cumplimiento_reportes;
    const elemento = document.getElementById('cumplimiento-reportes')?.parentElement;
    
    if (elemento) {
        elemento.classList.remove('text-success', 'text-warning', 'text-danger');
        
        if (cumplimiento >= 90) {
            elemento.classList.add('text-success');
        } else if (cumplimiento >= 70) {
            elemento.classList.add('text-warning');
        } else {
            elemento.classList.add('text-danger');
        }
    }
};

/**
 * Gráfico de progreso alternativo (sin Chart.js)
 */
const mostrarProgresoAlternativo = (datosProgreso) => {
    const container = document.getElementById('chartProgreso')?.parentElement;
    if (!container) return;
    
    if (!datosProgreso || datosProgreso.length === 0) {
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                <div class="text-center">
                    <i class="bi bi-graph-up fa-3x mb-3"></i>
                    <div>No hay datos de progreso disponibles</div>
                </div>
            </div>
        `;
        return;
    }
    
    // Agrupar por aplicación
    const aplicaciones = {};
    datosProgreso.forEach(item => {
        if (!aplicaciones[item.apl_nombre]) {
            aplicaciones[item.apl_nombre] = [];
        }
        aplicaciones[item.apl_nombre].push({
            fecha: item.ava_fecha,
            porcentaje: parseInt(item.ava_porcentaje)
        });
    });
    
    let html = '<div class="alternative-chart">';
    html += '<h6 class="mb-3">Progreso por Aplicación</h6>';
    
    Object.keys(aplicaciones).slice(0, 5).forEach((nombre, index) => {
        const datos = aplicaciones[nombre];
        const ultimoPorcentaje = datos[datos.length - 1]?.porcentaje || 0;
        const color = Object.values(COLORS.gradients)[index % Object.values(COLORS.gradients).length][0];
        
        html += `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-bold">${nombre}</small>
                    <small>${ultimoPorcentaje}%</small>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" style="width: ${ultimoPorcentaje}%; background-color: ${color};"></div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
};

/**
 * Gráfico de estados alternativo
 */
const mostrarEstadosAlternativo = (datosEstados) => {
    const container = document.getElementById('chartEstados')?.parentElement;
    if (!container) return;
    
    if (!datosEstados || datosEstados.length === 0) {
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                <div class="text-center">
                    <i class="bi bi-pie-chart fa-3x mb-3"></i>
                    <div>No hay datos de estados disponibles</div>
                </div>
            </div>
        `;
        return;
    }
    
    const total = datosEstados.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
    const colores = [COLORS.info, COLORS.success, COLORS.warning, COLORS.secondary];
    
    let html = '<div class="alternative-chart">';
    html += '<h6 class="mb-3">Distribución por Estado</h6>';
    
    datosEstados.forEach((item, index) => {
        const porcentaje = total > 0 ? ((parseInt(item.cantidad) / total) * 100).toFixed(1) : 0;
        const color = colores[index % colores.length];
        
        html += `
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div style="width: 12px; height: 12px; background-color: ${color}; border-radius: 50%; margin-right: 8px;"></div>
                        <small>${formatearEstado(item.apl_estado)}</small>
                    </div>
                    <small class="fw-bold">${item.cantidad} (${porcentaje}%)</small>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Actualizar leyenda
    actualizarLeyendaEstados(datosEstados, colores);
};

const actualizarLeyendaEstados = (datosEstados, colores) => {
    const contenedor = document.getElementById('leyenda-estados');
    if (!contenedor) return;
    
    const html = datosEstados.map((item, index) => `
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center">
                <div class="me-2" style="width: 12px; height: 12px; background-color: ${colores[index]}; border-radius: 50%;"></div>
                <small>${formatearEstado(item.apl_estado)}</small>
            </div>
            <small class="fw-bold">${item.cantidad}</small>
        </div>
    `).join('');
    
    contenedor.innerHTML = html;
};

/**
 * Mostrar error en todos los gráficos
 */
const mostrarErrorGraficos = () => {
    const graficos = ['chartProgreso', 'chartEstados', 'chartDesarrolladores'];
    
    graficos.forEach(id => {
        const container = document.getElementById(id)?.parentElement;
        if (container) {
            container.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <div class="text-center">
                        <i class="bi bi-graph-up fa-2x mb-2"></i>
                        <div>Error al cargar gráfico</div>
                    </div>
                </div>
            `;
        }
    });
};

/**
 * Gráficos con Chart.js (cuando funciona)
 */
const actualizarGraficoProgreso = async (datosProgreso) => {
    const canvas = document.getElementById('chartProgreso');
    if (!canvas || !Chart) return;
    
    const ctx = canvas.getContext('2d');
    
    if (chartProgreso) {
        chartProgreso.destroy();
    }
    
    if (!datosProgreso || datosProgreso.length === 0) {
        mostrarProgresoAlternativo(datosProgreso);
        return;
    }
    
    try {
        const aplicaciones = {};
        datosProgreso.forEach(item => {
            if (!aplicaciones[item.apl_nombre]) {
                aplicaciones[item.apl_nombre] = [];
            }
            aplicaciones[item.apl_nombre].push({
                x: dayjs(item.ava_fecha).format('DD/MM'),
                y: parseInt(item.ava_porcentaje)
            });
        });
        
        const datasets = Object.keys(aplicaciones).slice(0, 6).map((nombre, index) => {
            const colores = Object.values(COLORS.gradients);
            const color = colores[index % colores.length][0];
            
            return {
                label: nombre,
                data: aplicaciones[nombre],
                borderColor: color,
                backgroundColor: color + '20',
                fill: false,
                tension: 0.3
            };
        });
        
        chartProgreso = new Chart(ctx, {
            type: 'line',
            data: { datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'category',
                        title: {
                            display: true,
                            text: 'Fecha'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Progreso (%)'
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error al crear gráfico de progreso:', error);
        mostrarProgresoAlternativo(datosProgreso);
    }
};

const actualizarGraficoEstados = async (datosEstados) => {
    const canvas = document.getElementById('chartEstados');
    if (!canvas || !Chart) return;
    
    const ctx = canvas.getContext('2d');
    
    if (chartEstados) {
        chartEstados.destroy();
    }
    
    if (!datosEstados || datosEstados.length === 0) {
        mostrarEstadosAlternativo(datosEstados);
        return;
    }
    
    try {
        const labels = datosEstados.map(item => formatearEstado(item.apl_estado));
        const datos = datosEstados.map(item => parseInt(item.cantidad));
        const colores = [COLORS.info, COLORS.success, COLORS.warning, COLORS.secondary];
        
        chartEstados = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: datos,
                    backgroundColor: colores,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
        
        actualizarLeyendaEstados(datosEstados, colores);
    } catch (error) {
        console.error('Error al crear gráfico de estados:', error);
        mostrarEstadosAlternativo(datosEstados);
    }
};

const actualizarGraficoDesarrolladores = async (datosDesarrolladores) => {
    const canvas = document.getElementById('chartDesarrolladores');
    if (!canvas || !Chart) return;
    
    const ctx = canvas.getContext('2d');
    
    if (chartDesarrolladores) {
        chartDesarrolladores.destroy();
    }
    
    if (!datosDesarrolladores || datosDesarrolladores.length === 0) {
        mostrarDesarrolladoresAlternativo(datosDesarrolladores);
        return;
    }
    
    try {
        const top5 = datosDesarrolladores
            .filter(dev => dev.velocidad_semanal !== null)
            .sort((a, b) => parseFloat(b.velocidad_semanal) - parseFloat(a.velocidad_semanal))
            .slice(0, 5);
        
        const labels = top5.map(dev => dev.usu_nombre.split(' ')[0]);
        const datos = top5.map(dev => parseFloat(dev.velocidad_semanal) || 0);
        
        chartDesarrolladores = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Velocidad Semanal',
                    data: datos,
                    backgroundColor: COLORS.primary,
                    borderColor: COLORS.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Puntos/Semana'
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error al crear gráfico de desarrolladores:', error);
        mostrarDesarrolladoresAlternativo(datosDesarrolladores);
    }
};

// Funciones de acciones (simplificadas)
const verDetalleAplicacion = async (appId) => {
    const app = aplicacionesData.find(a => a.apl_id == appId);
    if (!app) return;
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Nombre:</strong></td><td>${app.apl_nombre || 'Sin nombre'}</td></tr>
                    <tr><td><strong>Descripción:</strong></td><td>${app.apl_descripcion || 'Sin descripción'}</td></tr>
                    <tr><td><strong>Responsable:</strong></td><td>${app.responsable_nombre || 'Sin asignar'} ${app.usu_grado ? `(${app.usu_grado})` : ''}</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="executive-badge ${obtenerClaseEstado(app.apl_estado)}">${formatearEstado(app.apl_estado)}</span></td></tr>
                    <tr><td><strong>Progreso:</strong></td><td>${app.ultimo_porcentaje || 0}% / ${app.apl_porcentaje_objetivo || 100}%</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Métricas de Rendimiento</h6>
                <table class="table table-sm">
                    <tr><td><strong>Velocidad semanal:</strong></td><td>${app.velocidad_semanal || 0} pts</td></tr>
                    <tr><td><strong>Días sin reporte:</strong></td><td>${app.dias_sin_reporte === 999 ? 'Nunca' : (app.dias_sin_reporte || 0) + ' días'}</td></tr>
                    <tr><td><strong>Bloqueadores activos:</strong></td><td>${app.bloqueadores_activos || 0}</td></tr>
                    <tr><td><strong>Semáforo:</strong></td><td>
                        <i class="bi bi-circle-fill text-${(app.semaforo || 'verde') === 'rojo' ? 'danger' : (app.semaforo || 'verde') === 'ambar' ? 'warning' : 'success'}"></i>
                        ${obtenerDescripcionSemaforo(app.semaforo || 'verde')}
                    </td></tr>
                </table>
            </div>
        </div>
    `;
    
    const modalElement = document.getElementById('contenido-detalle-app');
    if (modalElement) {
        modalElement.innerHTML = contenido;
        const modal = new Modal(document.getElementById('modalDetalleApp'));
        modal.show();
    }
};

const abrirAccionesRapidas = (appId) => {
    const modalElement = document.getElementById('modalAccionesRapidas');
    if (modalElement) {
        modalElement.dataset.appId = appId;
        const modal = new Modal(modalElement);
        modal.show();
    }
};

// Funciones auxiliares simplificadas
const mostrarCargando = (mostrar) => {
    const body = document.body;
    if (mostrar) {
        body.classList.add('loading-state');
    } else {
        body.classList.remove('loading-state');
    }
};

const iniciarActualizacionAutomatica = () => {
    intervaloActualizacion = setInterval(() => {
        if (document.visibilityState === 'visible') {
            cargarResumenEjecutivo();
            cargarAlertas();
        }
    }, 300000); // 5 minutos
};

const actualizarDatos = async () => {
    const btnActualizar = document.querySelector('[onclick="actualizarDatos()"]');
    if (btnActualizar) {
        const iconoOriginal = btnActualizar.innerHTML;
        
        btnActualizar.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Actualizando...';
        btnActualizar.disabled = true;
        
        try {
            await inicializarDashboard();
        } catch (error) {
            Toast.fire({
                icon: 'error',
                title: 'Error al actualizar los datos'
            });
        } finally {
            btnActualizar.innerHTML = iconoOriginal;
            btnActualizar.disabled = false;
        }
    }
};

const exportarReporte = () => {
    // Exportar tabla a CSV
    const csv = convertirTablaACSV();
    descargarCSV(csv, `reporte_aplicaciones_${dayjs().format('YYYY-MM-DD')}.csv`);
    Toast.fire({
        icon: 'success',
        title: 'Datos exportados correctamente'
    });
};

const convertirTablaACSV = () => {
    const headers = ['Aplicación', 'Responsable', 'Estado', 'Progreso', 'Velocidad', 'Último Reporte', 'Semáforo'];
    const rows = aplicacionesData.map(app => [
        app.apl_nombre,
        app.responsable_nombre || 'Sin asignar',
        formatearEstado(app.apl_estado),
        `${app.ultimo_porcentaje}%`,
        `${app.velocidad_semanal}`,
        app.dias_sin_reporte === 999 ? 'Nunca' : `${app.dias_sin_reporte} días`,
        app.semaforo
    ]);
    
    return [headers, ...rows].map(row => 
        row.map(field => `"${field}"`).join(',')
    ).join('\n');
};

const descargarCSV = (csv, filename) => {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
};

// Toast para notificaciones
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: 'white',
    color: COLORS.dark,
    customClass: {
        popup: 'executive-modal'
    }
});

// Exponer funciones globales necesarias
window.verDetalleAplicacion = verDetalleAplicacion;
window.abrirAccionesRapidas = abrirAccionesRapidas;
window.actualizarDatos = actualizarDatos;
window.cambiarPagina = cambiarPagina;
window.exportarReporte = exportarReporte;

// Función para limpiar al cerrar la página
window.addEventListener('beforeunload', () => {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
    }
    
    if (chartProgreso) chartProgreso.destroy();
    if (chartEstados) chartEstados.destroy();
    if (chartDesarrolladores) chartDesarrolladores.destroy();
});

// Exportar para uso en módulos
export {
    inicializarDashboard,
    cargarResumenEjecutivo,
    cargarAplicacionesCompletas,
    actualizarDatos
};