<div class="panel">
  <h3>{l s='Generar códigos QR' mod='qrsoldproducts'}</h3>
  <form method="post" action="" enctype="multipart/form-data">
    <div class="form-group">
      <label>{l s='Cantidad de códigos a generar' mod='qrsoldproducts'}</label>
      <input type="number" name="bulk_qr_count" value="10" min="1" class="form-control" required />
    </div>

    <div class="form-group">
      <label>{l s='Prefijo del código QR (opcional)' mod='qrsoldproducts'}</label>
      <input type="text" name="qr_prefix" class="form-control" placeholder="Ej: QSP-" maxlength="3" />
      <p class="help-block">{l s='Máximo 3 caracteres (A-Z, 0-9 o -).' mod='qrsoldproducts'}</p>
    </div>

    <button type="submit" name="submitGenerateQr" class="btn btn-primary">
      <i class="icon-cogs"></i> {l s='Generar' mod='qrsoldproducts'}
    </button>
    <a href="{$link->getAdminLink('AdminQrCodeManager')|escape:'htmlall'}" class="btn btn-default">
      {l s='Volver' mod='qrsoldproducts'}
    </a>
  </form>
</div>