import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import dayjs from 'dayjs';
import isoWeek from 'dayjs/plugin/isoWeek';
import 'dayjs/locale/es';

// Configurar dayjs en español y cargar plugin de semana
dayjs.extend(isoWeek);
dayjs.locale('es');

// Variables globales
let aplicacionesData = [];
let calendarioData = {};
let mesActual = new Date();
let aplicacionSeleccionada = null;

// URLs de las APIs
const urls = {
    aplicaciones: '/montoya_proyecto/API/desarrollador/aplicaciones',
    calendario: '/montoya_proyecto/API/desarrollador/calendario',
    resumen: '/montoya_proyecto/API/desarrollador/resumen',
    verificarDia: '/montoya_proyecto/API/desarrollador/verificarDia',
    guardarReporte: '/montoya_proyecto/API/avance/guardar',
    guardarInactividad: '/montoya_proyecto/API/inactividad/guardar',
    tiposInactividad: '/montoya_proyecto/API/inactividad/tipos'
};

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    inicializarDashboard();
    configurarEventListeners();
    cargarTiposInactividad();
});

// Función principal de inicialización
const inicializarDashboard = async () => {
    try {
        await Promise.all([
            cargarResumen(),
            cargarAplicaciones(),
            generarCalendario()
        ]);
        
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
    }
};

// Cargar resumen de estadísticas
const cargarResumen = async () => {
    try {
        const response = await fetch(urls.resumen);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            const data = resultado.data;
            document.getElementById('total-apps').textContent = data.total_apps || 0;
            document.getElementById('apps-progreso').textContent = data.en_progreso || 0;
            document.getElementById('apps-pausadas').textContent = data.pausadas || 0;
            document.getElementById('reportes-semana').textContent = data.reportes_semana || 0;
        }
    } catch (error) {
        console.error('Error al cargar resumen:', error);
    }
};

// Cargar aplicaciones del desarrollador
const cargarAplicaciones = async () => {
    try {
        const response = await fetch(urls.aplicaciones);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            aplicacionesData = resultado.data;
            renderizarAplicaciones();
        } else {
            document.getElementById('contenedor-aplicaciones').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-circle text-muted" style="font-size: 3rem;"></i>
                    <div class="mt-2 text-muted">${resultado.mensaje}</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
        document.getElementById('contenedor-aplicaciones').innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                <div class="mt-2">Error al cargar aplicaciones</div>
            </div>
        `;
    }
};

// Renderizar las aplicaciones
const renderizarAplicaciones = () => {
    const contenedor = document.getElementById('contenedor-aplicaciones');
    
    if (aplicacionesData.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <div class="mt-2 text-muted">No tienes aplicaciones asignadas</div>
            </div>
        `;
        return;
    }
    
    const html = aplicacionesData.map(app => {
        const semaforoClass = `semaforo-${app.semaforo}`;
        const estadoBadge = obtenerBadgeEstado(app.apl_estado);
        const porcentaje = app.ultimo_porcentaje || 0;
        
        // Iconos de estado para hoy
        let iconoEstadoHoy = '';
        let textoEstado = '';
        
        if (app.tiene_reporte_hoy) {
            iconoEstadoHoy = '<i class="bi bi-check-circle-fill text-success" title="Reporte completado"></i>';
            textoEstado = '✅ Con reporte';
        } else if (app.tiene_inactividad_hoy) {
            iconoEstadoHoy = '<i class="bi bi-exclamation-triangle-fill text-warning" title="Inactividad justificada"></i>';
            textoEstado = '⚠️ Justificado';
        } else {
            const esDiaHabil = dayjs().day() >= 1 && dayjs().day() <= 5;
            if (esDiaHabil && app.apl_estado === 'EN_PROGRESO') {
                iconoEstadoHoy = '<i class="bi bi-x-circle-fill text-danger" title="Sin reporte"></i>';
                textoEstado = '❌ Sin reporte';
            } else {
                iconoEstadoHoy = '<i class="bi bi-dash-circle-fill text-muted" title="No requerido"></i>';
                textoEstado = '➖ No requerido';
            }
        }
        
        return `
            <div class="card card-aplicacion mb-3 ${semaforoClass}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">${app.apl_nombre}</h6>
                        ${estadoBadge}
                    </div>
                    
                    <p class="card-text text-muted small mb-3">${app.apl_descripcion || 'Sin descripción'}</p>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Progreso</small>
                            <small class="fw-bold">${porcentaje}%</small>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: ${porcentaje}%"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center small mb-3">
                        <div class="col-4">
                            <div class="text-muted">Inicio</div>
                            <div class="fw-bold">${dayjs(app.apl_fecha_inicio).format('DD/MM/YY')}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Meta</div>
                            <div class="fw-bold">${app.apl_porcentaje_objetivo || 100}%</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Estado Hoy</div>
                            <div>${iconoEstadoHoy}</div>
                            <div class="text-xs">${textoEstado}</div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        ${generarBotonesAccion(app)}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    contenedor.innerHTML = html;
};

// Obtener badge de estado
const obtenerBadgeEstado = (estado) => {
    const badges = {
        'EN_PLANIFICACION': '<span class="badge bg-secondary estado-badge">Planificación</span>',
        'EN_PROGRESO': '<span class="badge bg-success estado-badge">En Progreso</span>',
        'PAUSADO': '<span class="badge bg-warning estado-badge">Pausado</span>',
        'CERRADO': '<span class="badge bg-info estado-badge">Cerrado</span>'
    };
    return badges[estado] || '<span class="badge bg-secondary estado-badge">Sin estado</span>';
};

// Generar botones de acción según el estado
const generarBotonesAccion = (app) => {
    const hoy = dayjs().format('YYYY-MM-DD');
    const esHoy = dayjs().format('YYYY-MM-DD') === hoy;
    const esDiaHabil = dayjs().day() >= 1 && dayjs().day() <= 5;
    
    let botones = [];
    
    botones.push(`
        <button class="btn btn-outline-primary btn-sm" onclick="seleccionarAplicacion(${app.apl_id})">
            <i class="bi bi-cursor me-1"></i>Seleccionar
        </button>
    `);
    
    if (app.tiene_reporte_hoy) {
        botones.push(`
            <button class="btn btn-success btn-sm" disabled>
                <i class="bi bi-check-circle me-1"></i>✅ Reportado hoy
            </button>
        `);
    } else if (app.tiene_inactividad_hoy) {
        botones.push(`
            <button class="btn btn-warning btn-sm" disabled>
                <i class="bi bi-exclamation-triangle me-1"></i>⚠️ Justificado hoy
            </button>
        `);
    } else if (esHoy && esDiaHabil && app.apl_estado === 'EN_PROGRESO') {
        botones.push(`
            <div class="btn-group" role="group">
                <button class="btn btn-success btn-sm" onclick="abrirModalReporte(${app.apl_id})">
                    <i class="bi bi-clipboard-data me-1"></i>Reportar
                </button>
                <button class="btn btn-warning btn-sm" onclick="abrirModalInactividad(${app.apl_id})">
                    <i class="bi bi-exclamation-triangle me-1"></i>Justificar
                </button>
            </div>
        `);
    } else if (!esDiaHabil) {
        botones.push(`
            <button class="btn btn-light btn-sm" disabled>
                <i class="bi bi-calendar-x me-1"></i>Fin de semana
            </button>
        `);
    } else if (app.apl_estado !== 'EN_PROGRESO') {
        botones.push(`
            <button class="btn btn-secondary btn-sm" disabled>
                <i class="bi bi-pause-circle me-1"></i>${app.apl_estado}
            </button>
        `);
    }
    
    return botones.join('');
};

// Generar calendario
const generarCalendario = async () => {
    try {
        const mesStr = dayjs(mesActual).format('YYYY-MM');
        const response = await fetch(`${urls.calendario}?mes=${mesStr}`);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            calendarioData = resultado.data;
            renderizarCalendario();
        }
    } catch (error) {
        console.error('Error al cargar calendario:', error);
    }
};

// Renderizar calendario
const renderizarCalendario = () => {
    const mesNombre = dayjs(mesActual).format('MMMM YYYY');
    document.getElementById('mes-actual').textContent = mesNombre;
    
    const calendario = document.getElementById('calendario');
    const primerDia = dayjs(mesActual).startOf('month');
    const ultimoDia = dayjs(mesActual).endOf('month');
    
    let primerDiaSemana = primerDia.day();
    if (primerDiaSemana === 0) primerDiaSemana = 7;
    
    let html = `
        <div class="row text-center small fw-bold mb-2">
            <div class="col">L</div>
            <div class="col">M</div>
            <div class="col">X</div>
            <div class="col">J</div>
            <div class="col">V</div>
            <div class="col text-muted">S</div>
            <div class="col text-muted">D</div>
        </div>
    `;
    
    let diaActual = primerDia.subtract(primerDiaSemana - 1, 'day');
    let contadorSemanas = 0;
    
    while (diaActual.isBefore(ultimoDia) || contadorSemanas < 6) {
        html += '<div class="row">';
        
        for (let i = 0; i < 7; i++) {
            const esDelMes = diaActual.month() === primerDia.month();
            const esHoy = diaActual.isSame(dayjs(), 'day');
            const esFinSemana = i >= 5;
            const fechaStr = diaActual.format('YYYY-MM-DD');
            
            let clasesDia = 'calendar-day';
            if (esHoy) clasesDia += ' today';
            if (esFinSemana) clasesDia += ' weekend';
            if (!esDelMes) clasesDia += ' text-muted';
            
            if (esDelMes && !esFinSemana) {
                const tieneReporte = calendarioData.reportes?.some(r => r.ava_fecha === fechaStr);
                const tieneInactividad = calendarioData.inactividades?.some(i => i.ina_fecha === fechaStr);
                
                if (tieneReporte) {
                    clasesDia += ' has-report';
                } else if (tieneInactividad) {
                    clasesDia += ' has-inactivity';
                } else if (diaActual.isBefore(dayjs(), 'day')) {
                    clasesDia += ' no-report';
                }
            }
            
            html += `
                <div class="col p-1">
                    <div class="${clasesDia}" onclick="clickDiaCalendario('${fechaStr}')">
                        ${diaActual.date()}
                    </div>
                </div>
            `;
            
            diaActual = diaActual.add(1, 'day');
        }
        
        html += '</div>';
        contadorSemanas++;
        
        if (diaActual.month() !== primerDia.month() && contadorSemanas >= 4) break;
    }
    
    calendario.innerHTML = html;
};

// Cambiar mes del calendario
const cambiarMes = (direccion) => {
    mesActual = dayjs(mesActual).add(direccion, 'month').toDate();
    generarCalendario();
};

// Click en día del calendario
const clickDiaCalendario = (fecha) => {
    const diaClickeado = dayjs(fecha);
    const hoy = dayjs();
    
    if (diaClickeado.isSame(hoy, 'day')) {
        if (aplicacionSeleccionada) {
            actualizarPanelAccionRapida();
        } else {
            Toast.fire({
                icon: 'info',
                title: 'Selecciona una aplicación primero'
            });
        }
    } else {
        mostrarInfoDia(fecha);
    }
};

// Mostrar información del día
const mostrarInfoDia = (fecha) => {
    const diaClickeado = dayjs(fecha);
    const fechaStr = diaClickeado.format('YYYY-MM-DD');
    
    const reportesDelDia = calendarioData.reportes?.filter(r => r.ava_fecha === fechaStr) || [];
    const inactividadesDelDia = calendarioData.inactividades?.filter(i => i.ina_fecha === fechaStr) || [];
    
    let contenido = `<h6>📅 ${diaClickeado.format('dddd, DD [de] MMMM [de] YYYY')}</h6>`;
    
    if (reportesDelDia.length > 0) {
        contenido += '<h6 class="text-success mt-3">✅ Reportes:</h6>';
        reportesDelDia.forEach(reporte => {
            contenido += `
                <div class="card card-body mb-2">
                    <strong>${reporte.apl_nombre}</strong><br>
                    <span class="badge bg-success">${reporte.ava_porcentaje}%</span>
                </div>
            `;
        });
    }
    
    if (inactividadesDelDia.length > 0) {
        contenido += '<h6 class="text-warning mt-3">⚠️ Inactividades:</h6>';
        inactividadesDelDia.forEach(inactividad => {
            contenido += `
                <div class="card card-body mb-2">
                    <strong>${inactividad.apl_nombre}</strong><br>
                    <span class="badge bg-warning">${inactividad.ina_tipo}</span><br>
                    <small>${inactividad.ina_motivo}</small>
                </div>
            `;
        });
    }
    
    if (reportesDelDia.length === 0 && inactividadesDelDia.length === 0) {
        contenido += '<p class="text-muted mt-3">No hay actividad registrada para este día</p>';
    }
    
    Swal.fire({
        title: 'Información del Día',
        html: contenido,
        width: 600,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar'
    });
};

// Seleccionar aplicación
const seleccionarAplicacion = (appId) => {
    aplicacionSeleccionada = aplicacionesData.find(app => app.apl_id == appId);
    if (aplicacionSeleccionada) {
        actualizarPanelAccionRapida();
        
        document.querySelectorAll('.card-aplicacion').forEach(card => {
            card.classList.remove('border-primary');
        });
        event.target.closest('.card-aplicacion').classList.add('border-primary');
    }
};

// Actualizar panel de acción rápida
const actualizarPanelAccionRapida = () => {
    const panel = document.getElementById('panel-accion-rapida');
    
    if (!aplicacionSeleccionada) {
        panel.innerHTML = `
            <div class="text-center py-3">
                <div class="text-muted">Selecciona una aplicación para continuar</div>
            </div>
        `;
        return;
    }
    
    const app = aplicacionSeleccionada;
    const hoy = dayjs().format('YYYY-MM-DD');
    const esDiaHabil = dayjs().day() >= 1 && dayjs().day() <= 5;
    
    let html = `
        <div class="mb-3">
            <h6 class="fw-bold">${app.apl_nombre}</h6>
            <small class="text-muted">Progreso actual: ${app.ultimo_porcentaje || 0}%</small>
        </div>
    `;
    
    if (!esDiaHabil) {
        html += `
            <div class="text-center py-3">
                <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                <div class="mt-2 text-muted">No es día hábil</div>
            </div>
        `;
    } else if (app.apl_estado !== 'EN_PROGRESO') {
        html += `
            <div class="text-center py-3">
                <i class="bi bi-pause-circle text-muted" style="font-size: 2rem;"></i>
                <div class="mt-2 text-muted">Aplicación ${app.apl_estado.toLowerCase()}</div>
            </div>
        `;
    } else if (app.tiene_reporte_hoy) {
        html += `
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Ya tienes reporte registrado para hoy
            </div>
        `;
    } else if (app.tiene_inactividad_hoy) {
        html += `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Ya tienes inactividad justificada para hoy
            </div>
        `;
    } else {
        html += `
            <div class="d-grid gap-2">
                <button class="btn btn-success" onclick="abrirModalReporte(${app.apl_id})">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Registrar Avance
                </button>
                <button class="btn btn-warning" onclick="abrirModalInactividad(${app.apl_id})">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Justificar Inactividad
                </button>
            </div>
        `;
    }
    
    panel.innerHTML = html;
};

// Abrir modal de reporte
const abrirModalReporte = (appId) => {
    const app = aplicacionesData.find(a => a.apl_id == appId);
    if (!app) return;
    
    document.getElementById('reporte_apl_id').value = app.apl_id;
    document.getElementById('reporte_fecha').value = dayjs().format('YYYY-MM-DD');
    document.getElementById('reporte_app_nombre').textContent = app.apl_nombre;
    document.getElementById('porcentaje_anterior_info').textContent = 
        `Porcentaje anterior: ${app.ultimo_porcentaje || 0}%`;
    
    document.getElementById('formReporte').reset();
    document.getElementById('reporte_apl_id').value = app.apl_id;
    document.getElementById('reporte_fecha').value = dayjs().format('YYYY-MM-DD');
    
    const modal = new Modal(document.getElementById('modalReporte'));
    modal.show();
};

// Abrir modal de inactividad
const abrirModalInactividad = (appId) => {
    const app = aplicacionesData.find(a => a.apl_id == appId);
    if (!app) return;
    
    document.getElementById('inactividad_apl_id').value = app.apl_id;
    document.getElementById('inactividad_fecha').value = dayjs().format('YYYY-MM-DD');
    document.getElementById('inactividad_app_nombre').textContent = app.apl_nombre;
    
    document.getElementById('formInactividad').reset();
    document.getElementById('inactividad_apl_id').value = app.apl_id;
    document.getElementById('inactividad_fecha').value = dayjs().format('YYYY-MM-DD');
    
    const modal = new Modal(document.getElementById('modalInactividad'));
    modal.show();
};

// Cargar tipos de inactividad
const cargarTiposInactividad = async () => {
    try {
        const response = await fetch(urls.tiposInactividad);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            const select = document.getElementById('inactividad_tipo');
            select.innerHTML = '<option value="">Seleccione el tipo...</option>';
            
            resultado.data.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.clave;
                option.textContent = tipo.descripcion;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar tipos de inactividad:', error);
    }
};

// Configurar event listeners
const configurarEventListeners = () => {
    document.getElementById('formReporte').addEventListener('submit', async (e) => {
        e.preventDefault();
        await guardarReporte();
    });
    
    document.getElementById('formInactividad').addEventListener('submit', async (e) => {
        e.preventDefault();
        await guardarInactividad();
    });
    
    document.getElementById('reporte_porcentaje').addEventListener('input', (e) => {
        const porcentajeNuevo = parseInt(e.target.value) || 0;
        const porcentajeAnterior = aplicacionSeleccionada?.ultimo_porcentaje || 0;
        
        const contenedorJustificacion = document.getElementById('contenedor_justificacion');
        const campoJustificacion = document.getElementById('reporte_justificacion');
        
        if (porcentajeNuevo < porcentajeAnterior) {
            contenedorJustificacion.style.display = 'block';
            campoJustificacion.required = true;
        } else {
            contenedorJustificacion.style.display = 'none';
            campoJustificacion.required = false;
            campoJustificacion.value = '';
        }
    });
};

// Guardar reporte
const guardarReporte = async () => {
    const btnGuardar = document.getElementById('btnGuardarReporte');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="bi bi-hourglass me-1"></i>Guardando...';
    
    try {
        const formData = new FormData(document.getElementById('formReporte'));
        const response = await fetch(urls.guardarReporte, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            await Swal.fire({
                icon: 'success',
                title: '¡Reporte guardado!',
                text: resultado.mensaje,
                showConfirmButton: true
            });
            
            Modal.getInstance(document.getElementById('modalReporte')).hide();
            await cargarAplicaciones();
            await generarCalendario();
            actualizarPanelAccionRapida();
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.mensaje,
                showConfirmButton: true
            });
        }
    } catch (error) {
        console.error('Error al guardar reporte:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar el reporte',
            showConfirmButton: true
        });
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save me-1"></i>Guardar Reporte';
    }
};

// Guardar inactividad
const guardarInactividad = async () => {
    const btnGuardar = document.getElementById('btnGuardarInactividad');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="bi bi-hourglass me-1"></i>Guardando...';
    
    try {
        const formData = new FormData(document.getElementById('formInactividad'));
        const response = await fetch(urls.guardarInactividad, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            await Swal.fire({
                icon: 'success',
                title: '¡Justificación guardada!',
                text: resultado.mensaje,
                showConfirmButton: true
            });
            
            Modal.getInstance(document.getElementById('modalInactividad')).hide();
            await cargarAplicaciones();
            await generarCalendario();
            actualizarPanelAccionRapida();
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.mensaje,
                showConfirmButton: true
            });
        }
    } catch (error) {
        console.error('Error al guardar justificación:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar la justificación',
            showConfirmButton: true
        });
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save me-1"></i>Guardar Justificación';
    }
};

// Toast para notificaciones rápidas
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// Exponer funciones globales
window.cargarAplicaciones = cargarAplicaciones;
window.cambiarMes = cambiarMes;
window.clickDiaCalendario = clickDiaCalendario;
window.seleccionarAplicacion = seleccionarAplicacion;
window.abrirModalReporte = abrirModalReporte;
window.abrirModalInactividad = abrirModalInactividad;