<div class="panel">
  <h3>Asignar QRs al pedido #{$id_order}</h3>
  <form method="post" action="{$link->getAdminLink('AdminQrCodeManager')}">
    <input type="hidden" name="controller" value="AdminQrCodeManager">
    <input type="hidden" name="token" value="{$token}">
    <input type="hidden" name="id_order" value="{$id_order}">

    <div class="form-group">
      <label>Prefijo para los códigos QR (opcional)</label>
      <input type="text" name="qr_prefix" class="form-control" maxlength="4">
    </div>

    <button type="submit" name="submit_assign_qrs" class="btn btn-primary">
      <i class="icon-check"></i> Generar y asignar QRs
    </button>

    <a href="{$back_link}" class="btn btn-default">Cancelar</a>
  </form>
</div>