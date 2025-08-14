import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/es';

// Configurar dayjs en español
dayjs.extend(relativeTime);
dayjs.locale('es');

// Paleta de colores para tema cemento/gris
const THEME_COLORS = {
    primary: '#3b82f6',
    success: '#059669',      // Verde esmeralda
    warning: '#d97706',      // Naranja tierra 
    danger: '#dc2626',       // Rojo ladrillo
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
let comentariosData = [];
let aplicacionesData = [];
let usuarioActual = 3; // ID del usuario actual, LO TENGO QUE CAMBIAR CUANDO YA TENGA EL LOGIN
let aplicacionSeleccionada = null;
let intervalActualizacion = null;

// URLs de las APIs
const urls = {
    comentarios: '/montoya_proyecto/API/comentarios/buscar',
    aplicaciones: '/montoya_proyecto/API/comentarios/aplicaciones',
    guardar: '/montoya_proyecto/API/comentarios/guardar',
    marcarLeido: '/montoya_proyecto/API/comentarios/marcarLeido',
    marcarTodosLeidos: '/montoya_proyecto/API/comentarios/marcarTodosLeidos',
    eliminar: '/montoya_proyecto/API/comentarios/eliminar',
    estadisticas: '/montoya_proyecto/API/comentarios/estadisticas'
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
        
        /* Estilos específicos para comentarios */
        .comentario-item {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid ${THEME_COLORS.border};
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .comentario-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .comentario-item.no-leido {
            border-left: 4px solid ${THEME_COLORS.primary};
            background: rgba(59, 130, 246, 0.05);
        }
        
        .comentario-item.propio {
            border-left: 4px solid ${THEME_COLORS.success};
            background: rgba(5, 150, 105, 0.05);
        }
        
        .comentario-autor {
            font-weight: 600;
            color: ${THEME_COLORS.text};
            margin-bottom: 0.25rem;
        }
        
        .comentario-fecha {
            font-size: 0.85rem;
            color: ${THEME_COLORS.textMuted};
            margin-bottom: 0.5rem;
        }
        
        .comentario-texto {
            color: ${THEME_COLORS.textSecondary};
            line-height: 1.5;
            white-space: pre-wrap;
        }
        
        .comentario-acciones {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .badge-no-leido {
            background: ${THEME_COLORS.primary};
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .comentario-contador {
            position: absolute;
            top: -8px;
            right: -8px;
            background: ${THEME_COLORS.danger};
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .lista-comentarios {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        .lista-comentarios::-webkit-scrollbar {
            width: 6px;
        }
        
        .lista-comentarios::-webkit-scrollbar-track {
            background: ${THEME_COLORS.bgTertiary};
            border-radius: 3px;
        }
        
        .lista-comentarios::-webkit-scrollbar-thumb {
            background: ${THEME_COLORS.concreteDark};
            border-radius: 3px;
        }
        
        .lista-comentarios::-webkit-scrollbar-thumb:hover {
            background: ${THEME_COLORS.steelBlue};
        }
        
        .form-comentario {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid ${THEME_COLORS.border};
            border-radius: 12px;
            padding: 1rem;
        }
        
        .aplicacion-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        
        .aplicacion-card:hover {
            border-color: ${THEME_COLORS.primary};
            transform: translateY(-2px);
        }
        
        .aplicacion-card.seleccionada {
            border-color: ${THEME_COLORS.primary};
            background: rgba(59, 130, 246, 0.05);
        }
    `;
    document.head.appendChild(style);
};

// Función principal de inicialización
const inicializarModulo = async () => {
    try {
        await cargarAplicaciones();
        
        Toast.fire({
            icon: 'success',
            title: 'Módulo de comentarios cargado correctamente'
        });
        
        // Iniciar actualización automática cada 30 segundos
        iniciarActualizacionAutomatica();
        
    } catch (error) {
        console.error('Error al inicializar módulo:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al cargar el módulo de comentarios'
        });
    }
};

// Cargar aplicaciones
const cargarAplicaciones = async () => {
    try {
        const response = await fetch(urls.aplicaciones);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            aplicacionesData = resultado.data;
            renderizarAplicaciones();
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
    }
};

// Renderizar lista de aplicaciones
const renderizarAplicaciones = () => {
    const contenedor = document.getElementById('lista-aplicaciones');
    
    if (aplicacionesData.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <div class="mt-2 text-muted">No hay aplicaciones disponibles</div>
            </div>
        `;
        return;
    }
    
    const html = aplicacionesData.map(app => {
        const estadoBadge = obtenerBadgeEstado(app.apl_estado);
        
        return `
            <div class="card aplicacion-card h-100" onclick="seleccionarAplicacion(${app.apl_id})">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">${app.apl_nombre}</h6>
                        ${estadoBadge}
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>
                            ${app.responsable_nombre || 'Sin asignar'}
                            ${app.usu_grado ? `(${app.usu_grado})` : ''}
                        </small>
                    </div>
                    
                    <div class="text-center">
                        <div class="badge-no-leido" id="contador-${app.apl_id}" style="display: none;">0</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    contenedor.innerHTML = html;
    
    // Cargar contadores de comentarios no leídos
    cargarContadoresNoLeidos();
};

// Cargar contadores de comentarios no leídos
const cargarContadoresNoLeidos = async () => {
    for (const app of aplicacionesData) {
        try {
            const response = await fetch(`${urls.comentarios}?apl_id=${app.apl_id}&usuario_id=${usuarioActual}&solo_no_leidos=true`);
            const resultado = await response.json();
            
            if (resultado.codigo === 1) {
                const contador = resultado.data.no_leidos;
                const elemento = document.getElementById(`contador-${app.apl_id}`);
                
                if (contador > 0) {
                    elemento.textContent = contador > 99 ? '99+' : contador;
                    elemento.style.display = 'flex';
                } else {
                    elemento.style.display = 'none';
                }
            }
        } catch (error) {
            console.error(`Error al cargar contador para app ${app.apl_id}:`, error);
        }
    }
};

// Seleccionar aplicación
const seleccionarAplicacion = (appId) => {
    aplicacionSeleccionada = aplicacionesData.find(app => app.apl_id == appId);
    
    if (aplicacionSeleccionada) {
        // Actualizar UI
        document.querySelectorAll('.aplicacion-card').forEach(card => {
            card.classList.remove('seleccionada');
        });
        event.target.closest('.aplicacion-card').classList.add('seleccionada');
        
        // Actualizar panel de comentarios
        document.getElementById('nombre-aplicacion-seleccionada').textContent = aplicacionSeleccionada.apl_nombre;
        document.getElementById('panel-comentarios').style.display = 'block';
        document.getElementById('mensaje-seleccionar').style.display = 'none';
        
        // Cargar comentarios
        cargarComentarios();
    }
};

// Cargar comentarios de la aplicación seleccionada
const cargarComentarios = async () => {
    if (!aplicacionSeleccionada) return;
    
    try {
        const response = await fetch(`${urls.comentarios}?apl_id=${aplicacionSeleccionada.apl_id}&usuario_id=${usuarioActual}`);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            comentariosData = resultado.data.comentarios;
            renderizarComentarios();
            
            // Actualizar contador
            const contador = document.getElementById(`contador-${aplicacionSeleccionada.apl_id}`);
            const noLeidos = resultado.data.no_leidos;
            
            if (noLeidos > 0) {
                contador.textContent = noLeidos > 99 ? '99+' : noLeidos;
                contador.style.display = 'flex';
            } else {
                contador.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error al cargar comentarios:', error);
        document.getElementById('lista-comentarios').innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                <div class="mt-2">Error al cargar los comentarios</div>
            </div>
        `;
    }
};

// Renderizar comentarios
const renderizarComentarios = () => {
    const contenedor = document.getElementById('lista-comentarios');
    
    if (comentariosData.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                <div class="mt-2 text-muted">No hay comentarios aún</div>
                <div class="text-muted">¡Sé el primero en comentar!</div>
            </div>
        `;
        return;
    }
    
    const html = comentariosData.map(comentario => {
        const esPropio = comentario.com_autor_id == usuarioActual;
        const esNoLeido = comentario.leido === 'f';
        const fechaRelativa = dayjs(comentario.com_creado_en).fromNow();
        const fechaCompleta = dayjs(comentario.com_creado_en).format('DD/MM/YYYY HH:mm');
        
        let clases = 'comentario-item';
        if (esPropio) clases += ' propio';
        else if (esNoLeido) clases += ' no-leido';
        
        return `
            <div class="${clases}" data-comentario-id="${comentario.com_id}">
                <div class="comentario-autor">
                    ${comentario.usu_nombre}
                    ${comentario.usu_grado ? `(${comentario.usu_grado})` : ''}
                    ${esPropio ? '<span class="badge bg-success ms-2">Tú</span>' : ''}
                    ${esNoLeido && !esPropio ? '<span class="badge bg-primary ms-2">Nuevo</span>' : ''}
                </div>
                
                <div class="comentario-fecha" title="${fechaCompleta}">
                    <i class="bi bi-clock me-1"></i>
                    ${fechaRelativa}
                </div>
                
                <div class="comentario-texto">
                    ${formatearTextoComentario(comentario.com_texto)}
                </div>
                
                <div class="comentario-acciones">
                    ${!esPropio && esNoLeido ? `
                        <button class="btn btn-sm btn-outline-primary" onclick="marcarComoLeido(${comentario.com_id})">
                            <i class="bi bi-check me-1"></i>Marcar como leído
                        </button>
                    ` : ''}
                    
                    ${esPropio ? `
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarComentario(${comentario.com_id})" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
    
    contenedor.innerHTML = html;
    
    // Hacer scroll hacia abajo
    contenedor.scrollTop = contenedor.scrollHeight;
};

// Formatear texto del comentario (detectar menciones, enlaces, etc.)
const formatearTextoComentario = (texto) => {
    // Detectar menciones @usuario
    texto = texto.replace(/@([a-zA-Z0-9_\.]+)/g, '<span class="badge bg-info">@$1</span>');
    
    // Detectar URLs simples
    texto = texto.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-primary">$1</a>');
    
    return texto;
};

// Configurar event listeners
const configurarEventListeners = () => {
    // Formulario de nuevo comentario
    document.getElementById('form-nuevo-comentario').addEventListener('submit', async (e) => {
        e.preventDefault();
        await enviarComentario();
    });
    
    // Auto-resize del textarea
    const textarea = document.getElementById('nuevo-comentario-texto');
    textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });
    
    // Contador de caracteres
    textarea.addEventListener('input', () => {
        const contador = document.getElementById('contador-caracteres');
        const longitud = textarea.value.length;
        contador.textContent = `${longitud}/1200`;
        
        if (longitud > 1200) {
            contador.classList.add('text-danger');
        } else if (longitud > 1000) {
            contador.classList.add('text-warning');
            contador.classList.remove('text-danger');
        } else {
            contador.classList.remove('text-warning', 'text-danger');
        }
    });
};

// Enviar nuevo comentario
const enviarComentario = async () => {
    if (!aplicacionSeleccionada) {
        Toast.fire({
            icon: 'error',
            title: 'Selecciona una aplicación primero'
        });
        return;
    }
    
    const texto = document.getElementById('nuevo-comentario-texto').value.trim();
    
    if (!texto) {
        Toast.fire({
            icon: 'error',
            title: 'El comentario no puede estar vacío'
        });
        return;
    }
    
    if (texto.length < 5) {
        Toast.fire({
            icon: 'error',
            title: 'El comentario debe tener al menos 5 caracteres'
        });
        return;
    }
    
    if (texto.length > 1200) {
        Toast.fire({
            icon: 'error',
            title: 'El comentario no puede exceder 1200 caracteres'
        });
        return;
    }
    
    const btnEnviar = document.getElementById('btn-enviar-comentario');
    btnEnviar.disabled = true;
    btnEnviar.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i>Enviando...';
    
    try {
        const formData = new FormData();
        formData.append('com_apl_id', aplicacionSeleccionada.apl_id);
        formData.append('com_autor_id', usuarioActual);
        formData.append('com_texto', texto);
        
        const response = await fetch(urls.guardar, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            // Limpiar formulario
            document.getElementById('nuevo-comentario-texto').value = '';
            document.getElementById('contador-caracteres').textContent = '0/1200';
            
            // Recargar comentarios
            await cargarComentarios();
            
            Toast.fire({
                icon: 'success',
                title: 'Comentario enviado correctamente'
            });
        } else {
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: resultado.mensaje
            });
        }
    } catch (error) {
        console.error('Error al enviar comentario:', error);
        await Swal.fire({
            ...swalConfig,
            icon: 'error',
            title: 'Error',
            text: 'Error al enviar el comentario'
        });
    } finally {
        btnEnviar.disabled = false;
        btnEnviar.innerHTML = '<i class="bi bi-send me-1"></i>Enviar';
    }
};

// Marcar comentario como leído
const marcarComoLeido = async (comentarioId) => {
    try {
        const formData = new FormData();
        formData.append('com_id', comentarioId);
        formData.append('usu_id', usuarioActual);
        
        const response = await fetch(urls.marcarLeido, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            // Recargar comentarios para actualizar el estado
            await cargarComentarios();
            
            Toast.fire({
                icon: 'success',
                title: 'Marcado como leído'
            });
        }
    } catch (error) {
        console.error('Error al marcar como leído:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al marcar como leído'
        });
    }
};

// Marcar todos los comentarios como leídos
const marcarTodosComoLeidos = async () => {
    if (!aplicacionSeleccionada) {
        Toast.fire({
            icon: 'error',
            title: 'Selecciona una aplicación primero'
        });
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('apl_id', aplicacionSeleccionada.apl_id);
        formData.append('usu_id', usuarioActual);
        
        const response = await fetch(urls.marcarTodosLeidos, {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            await cargarComentarios();
            
            Toast.fire({
                icon: 'success',
                title: resultado.mensaje
            });
        }
    } catch (error) {
        console.error('Error al marcar todos como leídos:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al marcar todos como leídos'
        });
    }
};

// Eliminar comentario
const eliminarComentario = async (comentarioId) => {
    const result = await Swal.fire({
        ...swalConfig,
        title: '¿Confirmar eliminación?',
        text: '¿Estás seguro de que quieres eliminar este comentario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: THEME_COLORS.danger
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('com_id', comentarioId);
            formData.append('usu_id', usuarioActual);
            
            const response = await fetch(urls.eliminar, {
                method: 'POST',
                body: formData
            });
            
            const resultado = await response.json();
            
            if (resultado.codigo === 1) {
                await cargarComentarios();
                
                Toast.fire({
                    icon: 'success',
                    title: 'Comentario eliminado correctamente'
                });
            } else {
                await Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: 'Error',
                    text: resultado.mensaje
                });
            }
        } catch (error) {
            console.error('Error al eliminar comentario:', error);
            await Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Error',
                text: 'Error al eliminar el comentario'
            });
        }
    }
};

// Obtener badge de estado de aplicación
const obtenerBadgeEstado = (estado) => {
    const badges = {
        'EN_PLANIFICACION': '<span class="badge bg-secondary">Planificación</span>',
        'EN_PROGRESO': '<span class="badge bg-success">En Progreso</span>',
        'PAUSADO': '<span class="badge bg-warning">Pausado</span>',
        'CERRADO': '<span class="badge bg-info">Cerrado</span>'
    };
    return badges[estado] || '<span class="badge bg-secondary">Sin estado</span>';
};

// Buscar comentarios
const buscarComentarios = async () => {
    const textoBusqueda = document.getElementById('buscar-comentarios').value.trim();
    
    if (!textoBusqueda) {
        // Si no hay texto de búsqueda, recargar comentarios normales
        await cargarComentarios();
        return;
    }
    
    if (!aplicacionSeleccionada) {
        Toast.fire({
            icon: 'error',
            title: 'Selecciona una aplicación primero'
        });
        return;
    }
    
    try {
        const response = await fetch(`${urls.comentarios}?apl_id=${aplicacionSeleccionada.apl_id}&usuario_id=${usuarioActual}&buscar=${encodeURIComponent(textoBusqueda)}`);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            comentariosData = resultado.data.comentarios;
            renderizarComentarios();
            
            if (comentariosData.length === 0) {
                document.getElementById('lista-comentarios').innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <div class="mt-2 text-muted">No se encontraron comentarios</div>
                        <div class="text-muted">Intenta con otros términos</div>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error al buscar comentarios:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al realizar la búsqueda'
        });
    }
};

// Ver estadísticas de comentarios
const verEstadisticas = async () => {
    if (!aplicacionSeleccionada) {
        Toast.fire({
            icon: 'error',
            title: 'Selecciona una aplicación primero'
        });
        return;
    }
    
    try {
        const response = await fetch(`${urls.estadisticas}?apl_id=${aplicacionSeleccionada.apl_id}`);
        const resultado = await response.json();
        
        if (resultado.codigo === 1) {
            const stats = resultado.data.estadisticas;
            const porUsuario = resultado.data.por_usuario;
            
            let contenidoUsuarios = '';
            if (porUsuario && porUsuario.length > 0) {
                contenidoUsuarios = porUsuario.map(usuario => `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${usuario.usu_nombre}</strong>
                            ${usuario.usu_grado ? `<small class="text-muted">(${usuario.usu_grado})</small>` : ''}
                        </div>
                        <span class="badge bg-primary">${usuario.total_comentarios}</span>
                    </div>
                `).join('');
            }
            
            const contenido = `
                <div class="text-start">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="text-primary">${stats.total_comentarios || 0}</h3>
                                    <div class="text-muted">Total Comentarios</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="text-success">${stats.usuarios_activos || 0}</h3>
                                    <div class="text-muted">Usuarios Activos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${contenidoUsuarios ? `
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Participación por Usuario</h6>
                            ${contenidoUsuarios}
                        </div>
                    ` : ''}
                    
                    <div class="mb-3">
                        <strong>Período:</strong><br>
                        ${stats.primer_comentario ? dayjs(stats.primer_comentario).format('DD/MM/YYYY') : 'N/A'} - 
                        ${stats.ultimo_comentario ? dayjs(stats.ultimo_comentario).format('DD/MM/YYYY') : 'N/A'}
                    </div>
                </div>
            `;
            
            await Swal.fire({
                ...swalConfig,
                title: '<i class="bi bi-bar-chart-fill me-2"></i>Estadísticas de Comentarios',
                html: contenido,
                width: 700,
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        await Swal.fire({
            ...swalConfig,
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar las estadísticas'
        });
    }
};

// Iniciar actualización automática
const iniciarActualizacionAutomatica = () => {
    intervalActualizacion = setInterval(async () => {
        // Solo actualizar si hay una aplicación seleccionada
        if (aplicacionSeleccionada) {
            await cargarComentarios();
        }
        
        // Actualizar contadores de aplicaciones
        await cargarContadoresNoLeidos();
    }, 30000); // Cada 30 segundos
};

// Detener actualización automática
const detenerActualizacionAutomatica = () => {
    if (intervalActualizacion) {
        clearInterval(intervalActualizacion);
        intervalActualizacion = null;
    }
};

// Limpiar al salir de la página
window.addEventListener('beforeunload', () => {
    detenerActualizacionAutomatica();
});

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
window.seleccionarAplicacion = seleccionarAplicacion;
window.marcarComoLeido = marcarComoLeido;
window.marcarTodosComoLeidos = marcarTodosComoLeidos;
window.eliminarComentario = eliminarComentario;
window.buscarComentarios = buscarComentarios;
window.verEstadisticas = verEstadisticas;
window.cargarComentarios = cargarComentarios;