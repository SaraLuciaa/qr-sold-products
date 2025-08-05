<h2 class="mb-4 text-primary">Configuración de Productos con QR</h2>

<form method="post" action="{$form_action}">
    <div class="card shadow-sm p-4 mb-4 border border-light rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <label class="form-check-label">
                <input type="checkbox" id="select_all_visible" class="form-check-input me-2">
                <strong>Seleccionar todos los productos visibles</strong>
            </label>
            <span id="counter" class="badge bg-primary">0 seleccionados</span>
            <button type="submit" name="submit_qr_config" class="btn btn-success ms-auto">
                Guardar configuración
            </button>
        </div>

        <table id="product_table" class="table table-hover table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th class="text-center">QR</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$products item=product}
                    <tr>
                        <td>{$product.name|escape:'html':'UTF-8'}</td>
                        <td class="text-center">
                            <input type="checkbox" name="products_with_qr[]"
                                   class="form-check-input product_checkbox"
                                   value="{$product.id_product}">
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</form>

{literal}
<!-- DataTables + Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        const selected = new Set({/literal}{$selected_ids_json nofilter}{literal});
        const table = $('#product_table').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'asc']],
            language: {
                search: "Buscar producto:",
                lengthMenu: "Mostrar _MENU_ productos por página",
                info: "Mostrando _START_ a _END_ de _TOTAL_ productos",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
               },
                zeroRecords: "No se encontraron productos",
                infoEmpty: "Mostrando 0 a 0 de 0 productos",
            },
            drawCallback: function () {
                updateCheckboxes();
            }
        });

        function updateCounter() {
            $('#counter').text(`${selected.size} seleccionados`);
        }

        function updateCheckboxes() {
            $('#product_table tbody input[type="checkbox"]').each(function () {
                const id = parseInt(this.value);
                this.checked = selected.has(id);
            });
        }

        // Listener de cambio individual
        $(document).on('change', '.product_checkbox', function () {
            const id = parseInt(this.value);
            if (this.checked) {
                selected.add(id);
            } else {
                selected.delete(id);
            }
            updateCounter();
        });

        // Selección por visibilidad
        $('#select_all_visible').on('change', function () {
            const visibleCheckboxes = table.rows({ search: 'applied' }).nodes().to$().find('.product_checkbox');
            visibleCheckboxes.each(function () {
                const id = parseInt(this.value);
                if ($('#select_all_visible').is(':checked')) {
                    selected.add(id);
                    this.checked = true;
                } else {
                    selected.delete(id);
                    this.checked = false;
                }
            });
            updateCounter();
        });

        // Al enviar el formulario, creamos campos ocultos con todos los seleccionados
        $('form').on('submit', function () {
            // Elimina anteriores
            $('input[name="products_with_qr[]"]').remove();

            // Agrega todos los seleccionados globalmente
            for (const id of selected) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'products_with_qr[]',
                    value: id
                }).appendTo(this);
            }
        });

        updateCheckboxes();
        updateCounter();
    });
</script>
{/literal}