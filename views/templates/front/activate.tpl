{extends file='page.tpl'}

{block name='page_header_container'}{/block}

{block name='page_content'}

<style>
.phone-number {
    font-family: monospace;
    font-weight: bold;
    color: #2c5aa0;
}
.country-prefix {
    color: #666;
    font-size: 0.9em;
}
.contact-buttons {
    margin-top: 10px;
}
.contact-buttons .btn {
    margin-right: 10px;
    margin-bottom: 5px;
}
.card-header {
    cursor: pointer;
    user-select: none;
    border-radius: 0.25rem 0.25rem 0 0;
}
.card-header:hover {
    background-color: #666;
}
.card {
    border-radius: 0.25rem;
}
.collapse-icon {
    float: right;
    transition: transform 0.2s;
}
.collapsed .collapse-icon {
    transform: rotate(-90deg);
}
.contact-item {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
}
.medical-section {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
    margin-top: 15px;
}
.btn {
    border-radius: 0.25rem;
}
</style>

{include file='module:qrsoldproducts/views/templates/front/locationhook.tpl'}

{if $pet}
    {* FOTO *}
    {if $pet.user_image}
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{$module_dir}/modules/qrsoldproducts/views/img/uploads/{$pet.user_image|escape:'url'}"
                 alt="Foto de {$pet.user_name|escape:'html'}"
                 style="
                    width: 180px;
                    height: 180px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 4px solid #dee2e6;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                 ">
        </div>
    {/if}


    {* NOMBRE *}
    <div style="text-align: center; margin-bottom: 10px;">
        <h1 class="h2">{$pet.user_name}</h1>
    </div>


    {* TIPO DE SANGRE Y ENFERMEDADES/ALERGIAS *}
    <div style="display: flex; flex-direction: row; justify-content: center; align-items: center; gap: 0; margin-bottom: 30px;">
        <div style="background: #fff; border-radius: 16px 0 0 16px; box-shadow: 0 2px 8px #0001; padding: 18px 32px; min-width: 180px; text-align: center; display: flex; flex-direction: column; align-items: center; border-right: 1px solid #eee;">
            <span style="font-size:1.5em; font-weight:700; color:#b94a48;">{if $pet.user_blood_type}{$pet.user_blood_type}{else}-{/if}</span>
            <span style="font-size:1em; color:#888; font-weight:400;">Tipo de sangre</span>
        </div>
        <div style="background: #fff; border-radius: 0 16px 16px 0; box-shadow: 0 2px 8px #0001; padding: 18px 32px; min-width: 180px; text-align: center; display: flex; flex-direction: column; align-items: center;">
            <span style="font-size:1.5em; font-weight:700; color:#2c5aa0;">{if $pet.edad neq ''}{$pet.edad}{else}-{/if}</span>
            <span style="font-size:1em; color:#888; font-weight:400;">Edad</span>
        </div>
    </div>

    {* DATOS PERSONALES - DESPLEGABLE *}
    <div class="card mb-4">
        <div class="card-header" data-toggle="collapse" data-target="#datosPersonales" aria-expanded="true" aria-controls="datosPersonales">
            <h2 class="h5 mb-0">
                Datos Personales
                <span class="collapse-icon">▼</span>
            </h2>
        </div>
        <div id="datosPersonales" class="collapse show">
            <ul class="list-group list-group-flush">
                {if $pet.user_type_dni}
                    <li class="list-group-item"><strong>Tipo de documento:</strong> 
                        {if $pet.user_type_dni == "CC"}Cédula de ciudadanía{/if}
                        {if $pet.user_type_dni == "TI"}Tarjeta de identidad{/if}
                        {if $pet.user_type_dni == "CE"}Cédula de extranjería{/if}
                    </li>
                {/if}
                {if $pet.user_dni}
                    <li class="list-group-item"><strong>Número de Documento:</strong> {$pet.user_dni}</li>
                {/if}

                {* Teléfonos del usuario *}
                {if $pet.user_mobile_number || $pet.user_home_number || $pet.user_work_number}
                    <li class="list-group-item">
                        {if $pet.user_mobile_number}
                            <strong>Celular:</strong> +{if $pet.mobile_prefix}{$pet.mobile_prefix}{/if}{$pet.user_mobile_number} <br>
                        {/if}
                        {if $pet.user_home_number}
                            <strong>Casa:</strong> +{if $pet.home_prefix}{$pet.home_prefix}{/if}{$pet.user_home_number} <br>
                        {/if}
                        {if $pet.user_work_number}
                            <strong>Trabajo:</strong> +{if $pet.work_prefix}{$pet.work_prefix}{/if}{$pet.user_work_number}
                        {/if}
                    </li>
                {/if}

                {if $pet.user_birthdate}
                    <li class="list-group-item"><strong>Fecha de Nacimiento:</strong> {$pet.user_birthdate}</li>
                {/if}
                {if $pet.user_gender}
                    <li class="list-group-item"><strong>Género:</strong> {$pet.user_gender}</li>
                {/if}
                {if $pet.user_stature_cm}
                    <li class="list-group-item"><strong>Estatura:</strong> {$pet.user_stature_cm} cm</li>
                {/if}
                {if $pet.user_weight_kg}
                    <li class="list-group-item"><strong>Peso:</strong> {$pet.user_weight_kg} kg</li>
                {/if}
                {if $pet.user_address}
                    <li class="list-group-item"><strong>Dirección:</strong> {$pet.user_address}</li>
                {/if}
                {if $pet.extra_notes}
                    <li class="list-group-item"><strong>Observaciones:</strong> {$pet.extra_notes}</li>
                {/if}
            </ul>
        </div>
    </div>

    {* CONTACTOS - DESPLEGABLE *}
    <div class="card mb-4">
        <div class="card-header" data-toggle="collapse" data-target="#contactos" aria-expanded="false" aria-controls="contactos">
            <h2 class="h5 mb-0">
                Contactos
                <span class="collapse-icon">▼</span>
            </h2>
        </div>
        <div id="contactos" class="collapse">
            <ul class="list-group list-group-flush">

                {* Contactos de emergencia *}
                {if $pet.contacts}
                    {foreach from=$pet.contacts item=contact}
                        <li class="list-group-item">
                                <strong>Nombre: </strong>{$contact.contact_name} <br>
                                {if $contact.relationship}<strong>Parentesco: </strong>{$contact.relationship}<br>{/if}
                                {if $contact.contact_email}
                                    <strong>Correo electrónico: </strong>{$contact.contact_email}<br>
                                {/if}
                                {if $contact.contact_phone_number || $contact.contact_phone_number_wp}
                                    <div class="contact-buttons">
                                        {if $contact.contact_phone_number}
                                            <a href="tel:+{if $contact.call_prefix}{$contact.call_prefix}{/if}{$contact.contact_phone_number}" 
                                               class="btn btn-primary btn-sm">
                                                LLAMAR
                                            </a>
                                        {/if}
                                        {if $contact.contact_phone_number_wp}
                                            <a href="https://wa.me/{if $contact.call_prefix_wp}{$contact.call_prefix_wp}{/if}{$contact.contact_phone_number_wp}?text={urlencode("Hola, he encontrado a ")}{urlencode($pet.user_name)}{urlencode(".")}" 
                                               target="_blank" 
                                               class="btn btn-success btn-sm">
                                                WHATSAPP
                                            </a>
                                        {/if}
                                {/if}
                            </div>
                        </li>
                    {/foreach}
                {/if}
            </ul>
        </div>
    </div>

    {* FICHA MÉDICA - DESPLEGABLE *}
    <div class="card mb-4">
        <div class="card-header" data-toggle="collapse" data-target="#fichaMedica" aria-expanded="false" aria-controls="fichaMedica">
            <h2 class="h5 mb-0">
                Ficha Médica
                <span class="collapse-icon">▼</span>
            </h2>
        </div>
        <div id="fichaMedica" class="collapse">
            <ul class="list-group list-group-flush">
                {* Información médica básica *}
                {if $pet.user_blood_type || $pet.user_accepts_transfusions || $pet.user_has_eps || $pet.user_has_prepaid || $pet.user_organ_donor}
                    <li class="list-group-item">
                        <div class="row">
                            {if $pet.user_blood_type}
                                <div class="col-md-6"><strong>Tipo de Sangre:</strong> {$pet.user_blood_type}</div>
                            {/if}
                            {if $pet.user_accepts_transfusions !== null}
                                <div class="col-md-6"><strong>Acepta Transfusiones:</strong> {if $pet.user_accepts_transfusions}Sí{else}No{/if}</div>
                            {/if}
                        </div>
                    </li>
                {/if}
                {if ($pet.user_has_eps == 1 && $pet.user_eps_name) || ($pet.user_has_prepaid == 1 && $pet.user_prepaid_name)}
                    <li class="list-group-item">
                        <div class="row">
                            {if $pet.user_has_eps == 1 && $pet.user_eps_name}
                                <div class="col-md-6"><strong>EPS:</strong> {$pet.user_eps_name}</div>
                            {/if}
                            {if $pet.user_has_prepaid == 1 && $pet.user_prepaid_name}
                                <div class="col-md-6"><strong>Prepagada:</strong> {$pet.user_prepaid_name}</div>
                            {/if}
                        </div>
                    </li>
                {/if}
                {if $pet.user_organ_donor !== null}
                    <li class="list-group-item">
                        <strong>Donador de Órganos:</strong> {if $pet.user_organ_donor}Sí{else}No{/if}
                    </li>
                {/if}

                {* Vacunación COVID-19 *}
                {if $pet.covid && $pet.covid.vaccinated}
                    <li class="list-group-item">
                        <strong>Vacunación COVID-19:</strong><br>
                            {if $pet.covid.doses}
                                <br><strong>Dosis:</strong> {$pet.covid.doses}
                            {/if}
                            {if $pet.covid.last_dose_date}
                                <br><strong>Última dosis:</strong> {$pet.covid.last_dose_date}
                            {/if}
                            {if $pet.covid.notes}
                                <br><strong>Notas:</strong> {$pet.covid.notes}
                            {/if}
                    </li>
                {/if}

                {* Condiciones médicas *}
                {if $pet.conditions && count($pet.conditions) > 0}
                    <li class="list-group-item">
                        <strong>Condiciones Médicas:</strong><br>
                        {foreach from=$pet.conditions item=condition}
                            {if $condition.condition_name}
                                • <strong>{$condition.condition_name}</strong>{if $condition.note}: {$condition.note}{/if}<br>
                            {/if}
                        {/foreach}
                    </li>
                {/if}

                {* Alergias *}
                {if $pet.allergies && count($pet.allergies) > 0}
                    <li class="list-group-item">
                        <strong>Alergias:</strong><br>
                        {foreach from=$pet.allergies item=allergy}
                            {if $allergy.allergen}
                                • <strong>{$allergy.allergen}</strong>{if $allergy.note}: {$allergy.note}{/if}<br>
                            {/if}
                        {/foreach}
                    </li>
                {/if}

                {* Medicamentos *}
                {if $pet.medications && count($pet.medications) > 0}
                    <li class="list-group-item">
                        <strong>Medicamentos:</strong><br>
                        {foreach from=$pet.medications item=medication}
                            {if $medication.med_name}
                                • <strong>{$medication.med_name}</strong>
                                {if $medication.dose} - Dosis: {$medication.dose}{/if}
                                {if $medication.frequency} - Frecuencia: {$medication.frequency}{/if}
                                {if $medication.note} - Nota: {$medication.note}{/if}<br>
                            {/if}
                        {/foreach}
                    </li>
                {/if}
            </ul>
        </div>
    </div>

    {* Botones de acción *}
    <div class="mt-4 text-center">
        {if $own}
            <a href="{$edit_link}" class="btn btn-warning btn-lg">
                Editar información
            </a>
        {/if}
    </div>

{else}
    <div class="alert alert-warning">
        No se encontró información para este código o el QR no está activo.
    </div>
{/if}

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar los íconos de colapso
    var collapsibles = document.querySelectorAll('[data-toggle="collapse"]');
    collapsibles.forEach(function(element) {
        element.addEventListener('click', function() {
            var target = document.querySelector(this.getAttribute('data-target'));
            var icon = this.querySelector('.collapse-icon');
            
            if (target.classList.contains('show')) {
                target.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
                this.classList.add('collapsed');
            } else {
                target.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
                this.classList.remove('collapsed');
            }
        });
    });
});
</script>

{/block}