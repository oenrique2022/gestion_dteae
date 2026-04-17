(function () {
    let chartMes = null;
    /** Filas del último top productos (para abrir el modal con el nombre correcto). */
    let ultimoTopProductos = [];
    let municipiosPorDepartamento = {};

    const fmt = (n) => new Intl.NumberFormat('es-ES').format(n);

    const esc = (s) => {
        if (s === null || s === undefined) return '';
        const d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    };

    const ymd = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    const setPreset = (preset) => {
        const hoy = new Date();
        let desde;
        let hasta = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

        switch (preset) {
            case 'mes':
                desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                break;
            case 'trimestre': {
                const q = Math.floor(hoy.getMonth() / 3);
                desde = new Date(hoy.getFullYear(), q * 3, 1);
                break;
            }
            case 'anio':
                desde = new Date(hoy.getFullYear(), 0, 1);
                break;
            case '12m':
                desde = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
                desde.setFullYear(desde.getFullYear() - 1);
                break;
            default:
                return;
        }

        document.getElementById('fechaDesdeDash').value = ymd(desde);
        document.getElementById('fechaHastaDash').value = ymd(hasta);
    };

    const mostrarError = (msg) => {
        const el = document.getElementById('dashboardError');
        if (!el) return;
        if (msg) {
            el.textContent = msg;
            el.classList.remove('d-none');
        } else {
            el.textContent = '';
            el.classList.add('d-none');
        }
    };

    const mostrarErrorRutas = (msg) => {
        const el = document.getElementById('calendarizacionRutasError');
        if (!el) return;
        if (msg) {
            el.textContent = msg;
            el.classList.remove('d-none');
        } else {
            el.textContent = '';
            el.classList.add('d-none');
        }
    };

    const getFiltrosTerritorio = () => {
        const departamentoId = document.getElementById('filtroDepartamentoDash')?.value || '';
        const municipioId = document.getElementById('filtroMunicipioDash')?.value || '';
        return { departamentoId, municipioId };
    };

    const poblarSelect = (selectEl, items, valueField = 'value', labelField = 'label') => {
        if (!selectEl) return;
        const valueActual = selectEl.value;
        selectEl.innerHTML = '<option value="">Todos</option>';
        (items || []).forEach((item) => {
            const opt = document.createElement('option');
            if (typeof item === 'object' && item !== null) {
                opt.value = String(item[valueField] ?? '');
                opt.textContent = String(item[labelField] ?? item[valueField] ?? '');
            } else {
                opt.value = String(item);
                opt.textContent = String(item);
            }
            selectEl.appendChild(opt);
        });
        if (valueActual && Array.from(selectEl.options).some((o) => o.value === valueActual)) {
            selectEl.value = valueActual;
        }
    };

    const actualizarMunicipiosPorDepartamento = () => {
        const dep = document.getElementById('filtroDepartamentoDash')?.value || '';
        const municipioEl = document.getElementById('filtroMunicipioDash');
        if (!municipioEl) return;
        const municipios = dep ? (municipiosPorDepartamento[dep] || []) : [];
        poblarSelect(municipioEl, municipios, 'id', 'nombre');
        municipioEl.disabled = !dep;
    };

    const cargarFiltrosTerritoriales = () => {
        const depEl = document.getElementById('filtroDepartamentoDash');
        const munEl = document.getElementById('filtroMunicipioDash');
        if (!depEl || !munEl) return Promise.resolve();

        return fetch('../app/ajax/reportes_ajax.php?action=filtros_territorio')
            .then((r) => r.json())
            .then((res) => {
                if (!res.success) return;
                const data = res.data || {};
                municipiosPorDepartamento = data.municipios_por_departamento || {};
                poblarSelect(depEl, data.departamentos || [], 'id', 'nombre');
                actualizarMunicipiosPorDepartamento();
            })
            .catch(() => {
                municipiosPorDepartamento = {};
                poblarSelect(depEl, []);
                poblarSelect(munEl, []);
                munEl.disabled = true;
            });
    };

    const abrirModalCentrosProducto = (idEquipo, nombreProducto) => {
        const desde = document.getElementById('fechaDesdeDash')?.value;
        const hasta = document.getElementById('fechaHastaDash')?.value;
        const filtros = getFiltrosTerritorio();
        if (!desde || !hasta) return;

        const modalEl = document.getElementById('modalCentrosPorProducto');
        const titulo = document.getElementById('modalCentrosPorProductoTitulo');
        const subtitulo = document.getElementById('modalCentrosPorProductoSubtitulo');
        const tbodyM = document.getElementById('modalTablaCentrosProducto');
        const cargando = document.getElementById('modalCentrosProductoCargando');
        const vacio = document.getElementById('modalCentrosProductoVacio');
        if (!modalEl || !tbodyM) return;

        if (titulo) titulo.textContent = nombreProducto || 'Producto';
        if (subtitulo) subtitulo.textContent = `Centros en el periodo ${desde} — ${hasta}`;
        tbodyM.innerHTML = '';
        if (vacio) vacio.classList.add('d-none');
        if (cargando) cargando.classList.remove('d-none');

        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();

        const url = `../app/ajax/reportes_ajax.php?action=centros_por_producto&id_equipo=${encodeURIComponent(idEquipo)}&fecha_desde=${encodeURIComponent(desde)}&fecha_hasta=${encodeURIComponent(hasta)}&departamento_id=${encodeURIComponent(filtros.departamentoId)}&municipio_id=${encodeURIComponent(filtros.municipioId)}`;

        fetch(url)
            .then((r) => r.json())
            .then((res) => {
                if (cargando) cargando.classList.add('d-none');
                if (!res.success) {
                    tbodyM.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">${esc(res.message || 'Error al cargar.')}</td></tr>`;
                    return;
                }
                const filas = res.data || [];
                if (!filas.length) {
                    if (vacio) vacio.classList.remove('d-none');
                    return;
                }
                filas.forEach((f) => {
                    const cod = f.codigo_infraestructura || '—';
                    const departamento = f.departamento || '—';
                    const municipio = f.municipio || '—';
                    tbodyM.innerHTML += `<tr>
                        <td>${esc(f.nombre_ce)}</td>
                        <td>${esc(`${departamento} / ${municipio}`)}</td>
                        <td>${esc(cod)}</td>
                        <td class="text-end">${fmt(f.unidades)}</td>
                        <td class="text-end">${fmt(f.num_entregas)}</td>
                    </tr>`;
                });
            })
            .catch(() => {
                if (cargando) cargando.classList.add('d-none');
                tbodyM.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Error de red.</td></tr>';
            });
    };

    const renderTablas = (data) => {
        const topBody = document.getElementById('tablaTopProductos');
        const estBody = document.getElementById('tablaPorEstado');
        if (topBody) {
            topBody.innerHTML = '';
            const rows = data.top_productos || [];
            ultimoTopProductos = rows.slice();
            if (!rows.length) {
                topBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3">Sin datos</td></tr>';
            } else {
                rows.forEach((r) => {
                    const idEq = String(r.id_equipo);
                    const nom = esc(r.nombre_equipo);
                    topBody.innerHTML += `<tr class="fila-producto-dashboard" style="cursor:pointer" role="button" tabindex="0" data-id-equipo="${idEq}" title="Ver centros que recibieron este producto">
                        <td>${nom}</td><td class="text-end">${fmt(r.total_cantidad)}</td>
                    </tr>`;
                });
            }
        }
        if (estBody) {
            estBody.innerHTML = '';
            const rows = data.por_estado || [];
            if (!rows.length) {
                estBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3">Sin datos</td></tr>';
            } else {
                rows.forEach((r) => {
                    const est = r.estado || '—';
                    estBody.innerHTML += `<tr><td>${esc(est)}</td><td class="text-end">${fmt(r.cantidad)}</td></tr>`;
                });
            }
        }
    };

    const renderChart = (porMes) => {
        const canvas = document.getElementById('chartEntregasMes');
        const sinDatos = document.getElementById('chartSinDatos');
        if (!canvas || typeof Chart === 'undefined') return;

        if (chartMes) {
            chartMes.destroy();
            chartMes = null;
        }

        const labels = (porMes || []).map((r) => r.periodo);
        const entregas = (porMes || []).map((r) => r.num_entregas);
        const unidades = (porMes || []).map((r) => r.unidades);

        if (!labels.length) {
            canvas.classList.add('d-none');
            if (sinDatos) sinDatos.classList.remove('d-none');
            return;
        }
        canvas.classList.remove('d-none');
        if (sinDatos) sinDatos.classList.add('d-none');

        chartMes = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'N° entregas',
                        data: entregas,
                        backgroundColor: 'rgba(13, 110, 253, 0.45)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Unidades',
                        data: unidades,
                        borderColor: 'rgba(25, 135, 84, 1)',
                        backgroundColor: 'rgba(25, 135, 84, 0.15)',
                        fill: false,
                        tension: 0.2,
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const v = ctx.raw;
                                return `${ctx.dataset.label}: ${fmt(v)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 0 }
                    },
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Entregas' },
                        ticks: { precision: 0 }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Unidades' },
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    };

    const renderCalendarizacionRutas = (data) => {
        const tbody = document.getElementById('tablaCalendarizacionRutas');
        const resumen = document.getElementById('resumenRutasEstado');
        if (!tbody || !resumen) return;

        const resumenEstado = data.resumen_estado || [];
        const rutas = data.rutas || [];

        if (!resumenEstado.length) {
            resumen.textContent = 'Sin rutas programadas en el rango.';
        } else {
            resumen.textContent = resumenEstado
                .map((r) => `${r.estado || '—'}: ${fmt(r.cantidad || 0)}`)
                .join(' | ');
        }

        tbody.innerHTML = '';
        if (!rutas.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin rutas programadas</td></tr>';
            return;
        }

        rutas.forEach((r) => {
            const fecha = r.fecha_programada || '—';
            const contrato = r.numero_contrato || `#${r.contrato_id || '—'}`;
            const centro = r.nombre_ce || `ID ${r.id_institucion || '—'}`;
            const ubicacion = `${r.departamento || '—'} / ${r.municipio || '—'}`;
            const responsable = r.responsable_entrega || '—';
            const motoristaVehiculo = `${r.motorista || '—'} / ${(r.vehiculo || '—')} (${r.placas || '—'})`;
            tbody.innerHTML += `<tr>
                <td>${esc(fecha)}</td>
                <td>${esc(r.estado || '—')}</td>
                <td>${esc(contrato)}</td>
                <td>${esc(centro)}</td>
                <td>${esc(ubicacion)}</td>
                <td>${esc(responsable)}</td>
                <td>${esc(motoristaVehiculo)}</td>
            </tr>`;
        });
    };

    const cargarCalendarizacionRutas = () => {
        const desde = document.getElementById('fechaDesdeDash')?.value;
        const hasta = document.getElementById('fechaHastaDash')?.value;
        const filtros = getFiltrosTerritorio();
        if (!desde || !hasta) return;

        mostrarErrorRutas('');
        const url = `../app/ajax/reportes_ajax.php?action=calendarizacion_rutas&fecha_desde=${encodeURIComponent(desde)}&fecha_hasta=${encodeURIComponent(hasta)}&departamento_id=${encodeURIComponent(filtros.departamentoId)}&municipio_id=${encodeURIComponent(filtros.municipioId)}`;

        fetch(url)
            .then((r) => r.json())
            .then((res) => {
                if (!res.success) {
                    mostrarErrorRutas(res.message || 'No se pudo cargar la calendarización.');
                    renderCalendarizacionRutas({ resumen_estado: [], rutas: [] });
                    return;
                }
                renderCalendarizacionRutas(res.data || {});
            })
            .catch(() => {
                mostrarErrorRutas('Error de red al cargar la calendarización de rutas.');
                renderCalendarizacionRutas({ resumen_estado: [], rutas: [] });
            });
    };

    const cargar = () => {
        const desde = document.getElementById('fechaDesdeDash')?.value;
        const hasta = document.getElementById('fechaHastaDash')?.value;
        const filtros = getFiltrosTerritorio();
        if (!desde || !hasta) return;

        mostrarError('');
        const url = `../app/ajax/reportes_ajax.php?action=resumen_gerencial&fecha_desde=${encodeURIComponent(desde)}&fecha_hasta=${encodeURIComponent(hasta)}&departamento_id=${encodeURIComponent(filtros.departamentoId)}&municipio_id=${encodeURIComponent(filtros.municipioId)}`;

        fetch(url)
            .then((r) => r.json())
            .then((res) => {
                if (!res.success) {
                    mostrarError(res.message || 'No se pudo cargar el resumen.');
                    return;
                }
                const d = res.data;
                const k = d.kpis || {};

                const elE = document.getElementById('kpiEntregas');
                const elU = document.getElementById('kpiUnidades');
                const elC = document.getElementById('kpiCentros');
                const elCo = document.getElementById('kpiContratos');
                if (elE) elE.textContent = fmt(k.total_entregas ?? 0);
                if (elU) elU.textContent = fmt(k.total_unidades ?? 0);
                if (elC) elC.textContent = fmt(k.centros_unicos ?? 0);
                if (elCo) elCo.textContent = fmt(k.contratos_unicos ?? 0);

                renderTablas(d);
                renderChart(d.por_mes || []);
                cargarCalendarizacionRutas();
            })
            .catch(() => {
                mostrarError('Error de red al cargar el dashboard.');
                cargarCalendarizacionRutas();
            });
    };

    document.addEventListener('DOMContentLoaded', function () {
        const hoy = new Date();
        const inicioAnio = new Date(hoy.getFullYear(), 0, 1);
        const desdeEl = document.getElementById('fechaDesdeDash');
        const hastaEl = document.getElementById('fechaHastaDash');
        if (desdeEl) desdeEl.value = ymd(inicioAnio);
        if (hastaEl) hastaEl.value = ymd(hoy);
        const municipioEl = document.getElementById('filtroMunicipioDash');
        if (municipioEl) municipioEl.disabled = true;

        document.getElementById('btnAplicarRango')?.addEventListener('click', cargar);
        document.getElementById('filtroDepartamentoDash')?.addEventListener('change', function () {
            actualizarMunicipiosPorDepartamento();
            cargar();
        });
        document.getElementById('filtroMunicipioDash')?.addEventListener('change', cargar);

        document.querySelectorAll('[data-preset]').forEach((btn) => {
            btn.addEventListener('click', function () {
                setPreset(this.getAttribute('data-preset'));
                cargar();
            });
        });

        const tablaTop = document.getElementById('tablaTopProductos');
        if (tablaTop) {
            tablaTop.addEventListener('click', function (ev) {
                const tr = ev.target.closest('tr.fila-producto-dashboard[data-id-equipo]');
                if (!tr) return;
                const id = tr.getAttribute('data-id-equipo');
                const row = ultimoTopProductos.find((x) => String(x.id_equipo) === id);
                abrirModalCentrosProducto(parseInt(id, 10), row ? row.nombre_equipo : '');
            });
            tablaTop.addEventListener('keydown', function (ev) {
                if (ev.key !== 'Enter' && ev.key !== ' ') return;
                const tr = ev.target.closest('tr.fila-producto-dashboard[data-id-equipo]');
                if (!tr) return;
                ev.preventDefault();
                tr.click();
            });
        }

        cargarFiltrosTerritoriales().finally(cargar);
    });
})();
