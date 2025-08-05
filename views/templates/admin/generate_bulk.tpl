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
      <p class="help-block">{l s='Máximo 3 caracteres.' mod='qrsoldproducts'}</p>
    </div>

    <div class="form-group">
      <label>{l s='Tamaño del código QR (px)' mod='qrsoldproducts'}</label>
      <input type="number" name="qr_size" value="100" min="100" max="800" class="form-control" required />
    </div>

    <div class="form-group">
      <label>{l s='Margen del código QR (px)' mod='qrsoldproducts'}</label>
      <input type="number" name="qr_margin" value="5" min="0" max="50" class="form-control" required />
    </div>

    <div class="form-group">
      <label>{l s='Versión del código QR (1-40)' mod='qrsoldproducts'}</label>
      <input type="number" name="qr_version" value="8" min="1" max="40" class="form-control" />
      <p class="help-block">
        {l s='Versión más alta = más módulos = más información en menos espacio. Ideal para QRs muy pequeños (ej: 0.5 mm).' mod='qrsoldproducts'}
      </p>
    </div>

    <div class="form-group">
      <label>
        <input type="checkbox" name="download_zip" value="1" />
        {l s='Descargar ZIP con los códigos generados' mod='qrsoldproducts'}
      </label>
    </div>

    <button type="submit" name="submitGenerateQr" class="btn btn-primary">
      <i class="icon-cogs"></i> {l s='Generar' mod='qrsoldproducts'}
    </button>
  </form>
</div>