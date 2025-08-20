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

    <form method="get" class="form-inline" style="gap:8px;margin-bottom:10px;">
      <input type="hidden" name="controller" value="AdminQrCodeManager">
      <input type="hidden" name="token" value="{$token}">
      <input type="hidden" name="assign_order_form" value="{$id_order}">
      <div class="form-group">
        <label for="q">Buscar</label>
        <input type="text" class="form-control" id="q" name="q" value="{$q|escape:'html'}" placeholder="Código o validación">
      </div>
      <div class="form-group">
        <label for="limit">Límite</label>
        <input type="number" class="form-control" id="limit" name="limit" value="{$limit|intval}" min="50" max="2000" step="50">
      </div>
      <button type="submit" class="btn btn-default">
        <i class="icon-search"></i> Filtrar
      </button>
    </form>

    <p>
      Debes asignar exactamente <strong id="required">{$total_required}</strong> QRs.
    </p>

    {if $total_required <= 0}
      <div class="alert alert-info">
        Este pedido no tiene productos configurados con QR o su cantidad requerida es 0.
      </div>
    {else}
      <form method="post" id="manualAssignForm">
        <input type="hidden" name="submit_manual_assign_qrs" value="1">
        <input type="hidden" name="id_order" value="{$id_order}">
        <input type="hidden" name="token" value="{$token}">

        <div style="max-height:420px; overflow:auto; border:1px solid #ddd; border-radius:6px;">
          <table class="table">
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
                <tr><td colspan="4" class="text-center text-muted">No hay QRs disponibles con ese filtro.</td></tr>
              {else}
                {foreach $available_qrs as $qr}
                  <tr>
                    <td>
                      <input type="checkbox" class="qr-box" name="selected_qrs[]" value="{$qr.id_qr_code}">
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

        <div style="display:flex;align-items:center;gap:12px;margin-top:10px;">
          <span>Seleccionados: <strong id="selectedCount">0</strong> / <strong>{$total_required}</strong></span>
          <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="icon-check"></i> Asignar manualmente
          </button>
          <a href="{$back_link}" class="btn btn-default">Cancelar</a>
        </div>
      </form>
    {/if}
  </div>
</div>

{literal}
<script>
(function(){
  var required = parseInt(document.getElementById('required')?.textContent || '0', 10);
  var form = document.getElementById('manualAssignForm');
  if(!form) return;

  var boxes = Array.from(form.querySelectorAll('.qr-box'));
  var submitBtn = document.getElementById('submitBtn');
  var selectedCountEl = document.getElementById('selectedCount');
  var checkAll = document.getElementById('checkAll');

  function updateState(){
    var count = boxes.filter(b => b.checked).length;
    selectedCountEl.textContent = String(count);
    submitBtn.disabled = (count !== required);
    if (count > required){
      selectedCountEl.style.color = '#c0392b';
    } else if (count === required){
      selectedCountEl.style.color = '#27ae60';
    } else {
      selectedCountEl.style.color = '';
    }
  }

  boxes.forEach(b => b.addEventListener('change', updateState));
  if (checkAll){
    checkAll.addEventListener('change', function(){
      boxes.forEach(b => b.checked = checkAll.checked);
      updateState();
    });
  }

  form.addEventListener('submit', function(e){
    var count = boxes.filter(b => b.checked).length;
    if (count !== required){
      e.preventDefault();
      alert('Debes seleccionar exactamente ' + required + ' QRs.');
    }
  });

  updateState();
})();
</script>
{/literal}