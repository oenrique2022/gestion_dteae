document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('tablaReportesEntregasBody');
    const inputBusqueda = document.getElementById('busquedaReportes');
    const modalEl = document.getElementById('modalResumenEntrega');
    if (!tbody) return;

    let todasLasFilas = [];

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

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value === null || value === undefined || value === '' ? '—' : String(value);
    };

    const textoFila = (row) => {
        const nombreCe = row.nombre_ce
            ? row.nombre_ce
            : `(Institución ID: ${row.id_institucion})`;
        const partes = [
            nombreCe,
            row.codigo_infraestructura,
            row.numero_contrato,
            row.nombre_contrato,
            row.nombre_proveedor,
            row.fuente_financiamiento,
            row.nombre_encargado,
            row.productos_entregados,
            row.fecha_entrega,
            row.estado,
            row.firma_responsable,
            row.comentarios_entrega,
            row.comentarios_contrato
        ];
        return partes
            .filter((p) => p !== null && p !== undefined && p !== '')
            .join(' ')
            .toLowerCase();
    };

    const abrirModalResumen = (row) => {
        if (!modalEl) return;

        const nombreCe = row.nombre_ce
            ? row.nombre_ce
            : `(Institución ID: ${row.id_institucion})`;
        setText('modal-ce-nombre', nombreCe);
        setText('modal-ce-codigo', row.codigo_infraestructura);

        setText('modal-entrega-fecha', row.fecha_entrega ? String(row.fecha_entrega).split(' ')[0] : '');
        setText('modal-entrega-estado', row.estado);
        setText('modal-entrega-firma', row.firma_responsable);
        setText('modal-entrega-productos', row.productos_entregados || '—');

        const wrapEnt = document.getElementById('modal-entrega-comentarios-wrap');
        const txtEnt = document.getElementById('modal-entrega-comentarios');
        if (wrapEnt && txtEnt) {
            const c = row.comentarios_entrega;
            if (c && String(c).trim()) {
                txtEnt.textContent = c;
                wrapEnt.classList.remove('d-none');
            } else {
                wrapEnt.classList.add('d-none');
            }
        }

        setText('modal-contrato-numero', row.numero_contrato);
        setText('modal-contrato-nombre', row.nombre_contrato);
        const ini = row.contrato_fecha_inicio ? String(row.contrato_fecha_inicio).split(' ')[0] : '';
        const fin = row.contrato_fecha_fin ? String(row.contrato_fecha_fin).split(' ')[0] : '';
        let vig = '—';
        if (ini && fin) vig = `${ini} — ${fin}`;
        else if (ini) vig = `Desde ${ini}`;
        else if (fin) vig = `Hasta ${fin}`;
        setText('modal-contrato-vigencia', vig);
        setText('modal-contrato-proveedor', row.nombre_proveedor);
        setText('modal-contrato-fuente', row.fuente_financiamiento);
        setText('modal-contrato-encargado', row.nombre_encargado);

        const wrapContr = document.getElementById('modal-contrato-comentarios-wrap');
        const txtContr = document.getElementById('modal-contrato-comentarios');
        if (wrapContr && txtContr) {
            const c = row.comentarios_contrato;
            if (c && String(c).trim()) {
                txtContr.textContent = c;
                wrapContr.classList.remove('d-none');
            } else {
                wrapContr.classList.add('d-none');
            }
        }

        const linkCompleto = document.getElementById('modal-link-contrato-completo');
        if (linkCompleto) {
            linkCompleto.href = `editar_contrato.php?id=${encodeURIComponent(row.id_contrato)}`;
        }

        const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    const renderTabla = (filas) => {
        tbody.innerHTML = '';
        const colspan = 8;
        if (!filas.length) {
            const msg = todasLasFilas.length
                ? 'Ningún resultado coincide con la búsqueda.'
                : 'No hay entregas registradas.';
            tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">${esc(msg)}</td></tr>`;
            return;
        }
        filas.forEach((row) => {
            const nombreCe = row.nombre_ce
                ? row.nombre_ce
                : `(Institución ID: ${row.id_institucion})`;
            const codigo = row.codigo_infraestructura || '—';
            const productos = row.productos_entregados
                ? row.productos_entregados
                : '—';
            const idEntregaAttr = String(row.id_entrega);
            tbody.innerHTML += `
                <tr>
                    <td>${esc(nombreCe)}</td>
                    <td>${esc(codigo)}</td>
                    <td>${esc(row.numero_contrato)}</td>
                    <td>${esc(row.nombre_contrato)}</td>
                    <td class="small text-break">${esc(productos)}</td>
                    <td>${fmtFecha(row.fecha_entrega)}</td>
                    <td>${esc(row.estado || '—')}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-ver-resumen" data-id-entrega="${idEntregaAttr}" title="Ver resumen de esta entrega">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    };

    tbody.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.btn-ver-resumen');
        if (!btn) return;
        const id = btn.getAttribute('data-id-entrega');
        const row = todasLasFilas.find((r) => String(r.id_entrega) === String(id));
        if (row) abrirModalResumen(row);
    });

    const aplicarFiltro = () => {
        const q = (inputBusqueda && inputBusqueda.value) ? inputBusqueda.value.trim().toLowerCase() : '';
        if (!q) {
            renderTabla(todasLasFilas);
            return;
        }
        const filtradas = todasLasFilas.filter((row) => textoFila(row).includes(q));
        renderTabla(filtradas);
    };

    fetch('../app/ajax/reportes_ajax.php?action=listar_entregas_centros')
        .then((response) => response.json())
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
                '<tr><td colspan="8" class="text-center text-danger">Error al cargar el reporte.</td></tr>';
        });

    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', aplicarFiltro);
    }
});
