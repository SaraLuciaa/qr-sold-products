{extends file='page.tpl'}

{block name='page_title'}
    Gestión de QRs
{/block}

{block name='page_content'}

<h2>Mis QRs Activos</h2>

{if $qrs|count}
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Código QR</th>
                <th>Nombre</th>
                <th>Documento</th>
                <th>Fecha de Nacimiento</th>
                <th>Sexo</th>
                <th>Fecha Activación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$qrs item=qr}
                <tr class="qr-row" onclick="location.href='{$link->getModuleLink('qrsoldproducts', 'activate', ['code' => $qr.code, 'own' => 1])|escape:'html'}'">
                    <td>{$qr.code}</td>
                    <td>{$qr.user_name}</td>
                    <td>{$qr.user_type_dni} {$qr.user_dni}</</td>
                    <td>{$qr.user_birthdate}</td>
                    <td>{$qr.user_gender}</td>
                    <td>{$qr.date_activated}</td>
                    <td>
                        <a href="{$link->getModuleLink('qrsoldproducts', 'activate', ['code' => $qr.code, 'own' => 1])|escape:'html'}"
                        class="btn btn-sm btn-info mr-2"
                        onclick="event.stopPropagation();">
                            Ver
                        </a>
                        <a href="{$add_qr_link}?edit_id={$qr.id_customer_code}" 
                        class="btn btn-sm btn-warning"
                        onclick="event.stopPropagation();">
                            Editar
                        </a>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{else}
    <p>No tienes QRs activados aún.</p>
{/if}

<a href="{$add_qr_link}" class="btn btn-primary mt-3">
    Agregar nuevo QR
</a>

{literal}
<style>
.qr-row {
    cursor: pointer;
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.qr-row:hover {
    background-color: #f9f9f9;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
</style>
{/literal}

{/block}
