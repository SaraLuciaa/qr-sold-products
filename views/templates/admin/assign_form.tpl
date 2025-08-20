{* views/templates/admin/assign_form.tpl *}

<div class="panel">
  <div class="panel-heading">
    Asignar QRs manualmente al pedido #{$id_order}
  </div>

  <div class="panel-body">
    <a href="{$back_link}" class="btn btn-default">
      <i class="icon-arrow-left"></i> Volver al pedido
    </a>

    <hr>

    {* ---------- FORM DE BUSQUEDA ---------- *}
    <form method="get" class="form-inline qr-search-box">
      <input type="hidden" name="controller" value="AdminQrCodeManager">
      <input type="hidden" name="token" value="{$token}">
      <input type="hidden" name="assign_order_form" value="{$id_order}">

      <div class="input-group">
        <span class="input-group-addon"><i class="icon-search"></i></span>
        <input type="text" class="form-control" id="q" name="q"
               value="{$q|escape:'html'}"
               placeholder="Buscar código o validación...">
      </div>

      <div class="input-group ml-2">
        <span class="input-group-addon"><i class="icon-list-ol"></i></span>
        <input type="number" class="form-control" id="limit" name="limit"
               value="{$limit|intval}" min="50" max="2000" step="50"
               placeholder="Límite">
      </div>

      <button type="submit" class="btn btn-primary ml-2">
        <i class="icon-filter"></i> Filtrar
      </button>
    </form>

    <p class="qr-required-info">
      Debes asignar exactamente
      <span class="badge badge-info" id="required">{$total_required}</span>
      QRs.
    </p>

    {if $total_required <= 0}
      <div class="alert alert-info">
        Este pedido no tiene productos configurados con QR o su cantidad requerida es 0.
      </div>
    {else}
      {* ---------- FORM DE ASIGNACION ---------- *}
      <form method="post" id="manualAssignForm">
        <input type="hidden" name="submit_manual_assign_qrs" value="1">
        <input type="hidden" name="id_order" value="{$id_order}">
        <input type="hidden" name="token" value="{$token}">

        <div class="qr-table-wrapper">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th style="width:48px;">
                  <input type="checkbox" id="checkAll">
                </th>
                <th>Código</th>
                <th>Validación</th>
                <th>Fecha creación</th>
              </tr>
            </thead>
            <tbody>
              {if $available_qrs|@count == 0}
                <tr><td colspan="4" class="text-center text-muted">
                  <i class="icon-warning"></i> No hay QRs disponibles con ese filtro.
                </td></tr>
              {else}
                {foreach $available_qrs as $qr}
                  <tr>
                    <td>
                      <input type="checkbox" class="qr-box" value="{$qr.id_qr_code}">
                    </td>
                    <td><code>{$qr.code}</code></td>
                    <td><span class="label label-default">{$qr.validation_code}</span></td>
                    <td>{$qr.date_created}</td>
                  </tr>
                {/foreach}
              {/if}
            </tbody>
          </table>
        </div>

        <div class="qr-actions">
          <span>Seleccionados:
            <span class="badge badge-secondary" id="selectedCount">0</span>
            / <strong>{$total_required}</strong>
          </span>
          <button type="submit" class="btn btn-success" id="submitBtn" disabled>
            <i class="icon-check"></i> Asignar manualmente
          </button>
          <a href="{$back_link}" class="btn btn-outline-secondary">
            <i class="icon-times"></i> Cancelar
          </a>
        </div>
      </form>
    {/if}
  </div>
</div>

{* ---------- ESTILOS ---------- *}
<style>
.qr-search-box {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 15px;
}
.qr-table-wrapper {
  max-height: 420px;
  overflow: auto;
  border: 1px solid #ddd;
  border-radius: 8px;
}
.qr-table-wrapper table tbody tr:hover {
  background-color: #f7faff;
}
.qr-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 12px;
}
.qr-actions .badge {
  font-size: 14px;
  padding: 6px 10px;
}
</style>

{* ---------- SCRIPT ---------- *}
{literal}
<script>
(function(){
  var required = parseInt(document.getElementById('required')?.textContent || '0', 10);
  var form = document.getElementById('manualAssignForm');
  if(!form) return;

  var submitBtn = document.getElementById('submitBtn');
  var selectedCountEl = document.getElementById('selectedCount');
  var checkAll = document.getElementById('checkAll');

  var STORAGE_KEY = "selected_qrs_" + form.querySelector('[name="id_order"]').value;
  var selected = new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]"));

  function syncUI(){
    var boxes = Array.from(document.querySelectorAll('.qr-box'));
    boxes.forEach(b => b.checked = selected.has(b.value));

    var count = selected.size;
    selectedCountEl.textContent = count;
    submitBtn.disabled = (count !== required);

    if (count > required){
      selectedCountEl.className = "badge badge-danger";
    } else if (count === required){
      selectedCountEl.className = "badge badge-success";
    } else {
      selectedCountEl.className = "badge badge-warning";
    }
  }

  document.addEventListener('change', function(e){
    if (e.target.classList.contains('qr-box')){
      if (e.target.checked){
        selected.add(e.target.value);
      } else {
        selected.delete(e.target.value);
      }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(selected)));
      syncUI();
    }
  });

  if (checkAll){
    checkAll.addEventListener('change', function(){
      var boxes = Array.from(document.querySelectorAll('.qr-box'));
      boxes.forEach(b => {
        if (checkAll.checked){ selected.add(b.value); }
        else { selected.delete(b.value); }
      });
      localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(selected)));
      syncUI();
    });
  }

  form.addEventListener('submit', function(e){
    if (selected.size !== required){
      e.preventDefault();
      alert('Debes seleccionar exactamente ' + required + ' QRs.');
    } else {
      selected.forEach(id => {
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'selected_qrs[]';
        hidden.value = id;
        form.appendChild(hidden);
      });
    }
  });

  syncUI();
})();
</script>
{/literal}