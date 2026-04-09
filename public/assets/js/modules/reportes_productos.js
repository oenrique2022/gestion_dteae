document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('tablaReportesProductosBody');
    const inputBusqueda = document.getElementById('busquedaReportesProductos');
    const modalEl = document.getElementById('modalDetalleProductoEntrega');
    if (!tbody) return;

    let todasLasFilas = [];
    let vistaActual = [];

    const esc = (s) => {
        if (s === null || s === undefined) return '';
        const d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    };

    const fmtFecha = (f) => {
        if (!f) return '—';
        const part = String(f).split(' ')[0];
        return esc(part);
    };

    const textoFila = (row) => {
        const nombreCe = row.nombre_ce
            ? row.nombre_ce
            : `(Institución ID: ${row.id_institucion})`;
        const partes = [
            row.nombre_equipo,
            row.nombre_tipo_equipo,
            row.codigo_equipo,
            row.cantidad,
            nombreCe,
            row.codigo_infraestructura,
            row.numero_contrato,
            row.nombre_contrato,
            row.fecha_entrega,
            row.estado,
            row.detalle_comentario
        ];
        return partes
            .filter((p) => p !== null && p !== undefined && p !== '')
            .join(' ')
            .toLowerCase();
    };

    const abrirModal = (row) => {
        if (!modalEl) return;

        const set = (id, v) => {
            const el = document.getElementById(id);
            if (el) el.textContent = v === null || v === undefined || v === '' ? '—' : String(v);
        };

        set('modal-prod-nombre', row.nombre_equipo);
        set('modal-prod-tipo', row.nombre_tipo_equipo);
        set('modal-prod-codigo', row.codigo_equipo);
        set('modal-prod-cantidad', row.cantidad);

        const nombreCe = row.nombre_ce
            ? row.nombre_ce
            : `(Institución ID: ${row.id_institucion})`;
        set('modal-prod-ce', nombreCe);
        set('modal-prod-infra', row.codigo_infraestructura);

        set('modal-prod-contrato-num', row.numero_contrato);
        set('modal-prod-contrato-nom', row.nombre_contrato);
        set('modal-prod-fecha', row.fecha_entrega ? String(row.fecha_entrega).split(' ')[0] : '');
        set('modal-prod-estado', row.estado);

        const wrap = document.getElementById('modal-prod-comentario-wrap');
        const txt = document.getElementById('modal-prod-comentario');
        if (wrap && txt) {
            const c = row.detalle_comentario;
            if (c && String(c).trim()) {
                txt.textContent = c;
                wrap.classList.remove('d-none');
            } else {
                wrap.classList.add('d-none');
            }
        }

        const link = document.getElementById('modal-prod-link-contrato');
        if (link) link.href = `editar_contrato.php?id=${encodeURIComponent(row.id_contrato)}`;

        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    const renderTabla = (filas) => {
        vistaActual = filas;
        tbody.innerHTML = '';
        const colspan = 10;
        if (!filas.length) {
            const msg = todasLasFilas.length
                ? 'Ningún resultado coincide con la búsqueda.'
                : 'No hay líneas de productos en entregas.';
            tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">${esc(msg)}</td></tr>`;
            return;
        }
        filas.forEach((row, idx) => {
            const nombreCe = row.nombre_ce
                ? row.nombre_ce
                : `(Institución ID: ${row.id_institucion})`;
            const tipo = row.nombre_tipo_equipo || '—';
            const codProd = row.codigo_equipo || '—';
            tbody.innerHTML += `
                <tr>
                    <td class="text-break">${esc(row.nombre_equipo)}</td>
                    <td>${esc(tipo)}</td>
                    <td>${esc(codProd)}</td>
                    <td class="text-end">${esc(String(row.cantidad))}</td>
                    <td class="text-break">${esc(nombreCe)}</td>
                    <td>${esc(row.codigo_infraestructura || '—')}</td>
                    <td class="text-break">${esc(row.numero_contrato)}</td>
                    <td>${fmtFecha(row.fecha_entrega)}</td>
                    <td>${esc(row.estado || '—')}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-detalle-prod" data-idx="${idx}" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    };

    tbody.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.btn-detalle-prod');
        if (!btn) return;
        const idx = parseInt(btn.getAttribute('data-idx'), 10);
        if (Number.isNaN(idx) || !vistaActual[idx]) return;
        abrirModal(vistaActual[idx]);
    });

    const aplicarFiltro = () => {
        const q = (inputBusqueda && inputBusqueda.value) ? inputBusqueda.value.trim().toLowerCase() : '';
        if (!q) {
            renderTabla(todasLasFilas);
            return;
        }
        renderTabla(todasLasFilas.filter((row) => textoFila(row).includes(q)));
    };

    fetch('../app/ajax/reportes_ajax.php?action=listar_productos_por_centro')
        .then((r) => r.json())
        .then((data) => {
            if (data.success && data.data && data.data.length > 0) {
                todasLasFilas = data.data;
                aplicarFiltro();
            } else {
                todasLasFilas = [];
                renderTabla([]);
            }
        })
        .catch(() => {
            todasLasFilas = [];
            tbody.innerHTML =
                '<tr><td colspan="10" class="text-center text-danger">Error al cargar el informe.</td></tr>';
        });

    if (inputBusqueda) inputBusqueda.addEventListener('input', aplicarFiltro);
});
