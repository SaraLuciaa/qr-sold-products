
{extends file='page.tpl'}

{block name='page_title'}QR de Persona{/block}

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
</style>

{include file='module:qrsoldproducts/views/templates/front/locationhook.tpl'}

{if isset($error_message)}
    <div class="alert alert-danger">
        {$error_message}
    </div>
{/if}

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

{if $pet}
    <div class="card mb-4">
        <div class="card-header"><h2 class="h5">Información Personal</h2></div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Nombre Completo:</strong> {$pet.user_name}</li>
            <li class="list-group-item"><strong>Tipo de documento:</strong> 
                {if $pet.user_type_dni == "CC"}Cédula de ciudadanía{/if}
                {if $pet.user_type_dni == "TI"}Tarjeta de identidad{/if}
                {if $pet.user_type_dni == "CE"}Cédula de extranjería{/if}
            </li>
            <li class="list-group-item"><strong>Número de Documento:</strong> {$pet.user_dni}</li>
            <li class="list-group-item"><strong>Fecha de Nacimiento:</strong> {$pet.user_birthdate}</li>
            <li class="list-group-item"><strong>Género:</strong> {$pet.user_gender}</li>
            <li class="list-group-item"><strong>Estatura:</strong> {$pet.user_stature_cm} cm</li>
            <li class="list-group-item"><strong>Dirección:</strong> {$pet.user_address}</li>
            <li class="list-group-item"><strong>Teléfono celular:</strong> 
                {if $pet.user_mobile_number}
                    <span class="phone-number">
                        {if $pet.mobile_prefix}<span class="country-prefix">+{$pet.mobile_prefix}</span> {/if}{$pet.user_mobile_number}
                    </span>
                {else}No especificado{/if}
            </li>
            <li class="list-group-item"><strong>Teléfono residencial:</strong> 
                {if $pet.user_home_number}
                    <span class="phone-number">
                        {if $pet.home_prefix}<span class="country-prefix">+{$pet.home_prefix}</span> {/if}{$pet.user_home_number}
                    </span>
                {else}No especificado{/if}
            </li>
            <li class="list-group-item"><strong>Teléfono del trabajo:</strong> 
                {if $pet.user_work_number}
                    <span class="phone-number">
                        {if $pet.work_prefix}<span class="country-prefix">+{$pet.work_prefix}</span> {/if}{$pet.user_work_number}
                    </span>
                {else}No especificado{/if}
            </li>
            <li class="list-group-item"><strong>Peso:</strong> {$pet.user_weight_kg} kg</li>
            <li class="list-group-item"><strong>EPS:</strong> {if $pet.user_has_eps == 1}{$pet.user_eps_name}{else}No tiene{/if}</li>
            <li class="list-group-item"><strong>Prepagada:</strong> {if $pet.user_has_prepaid == 1}{$pet.user_prepaid_name}{else}No tiene{/if}</li>
            <li class="list-group-item"><strong>Tipo de Sangre:</strong> {$pet.user_blood_type}</li>
            <li class="list-group-item"><strong>Acepta Transfusiones:</strong> {if $pet.user_accepts_transfusions}Sí{else}No{/if}</li>
            <li class="list-group-item"><strong>Donador de Órganos:</strong> {if $pet.user_organ_donor}Sí{else}No{/if}</li>
            <li class="list-group-item"><strong>Observaciones:</strong> {$pet.extra_notes}</li>
        </ul>
    </div>

    {if $pet.contacts}
        <div class="card mb-4">
            <div class="card-header"><h2 class="h5">Contactos de Emergencia</h2></div>
            <ul class="list-group list-group-flush">
                {foreach from=$pet.contacts item=contact}
                    <li class="list-group-item">
                        <strong>Nombre:</strong> {$contact.contact_name}<br>
                        <strong>Teléfono:</strong> 
                            {if $contact.contact_phone_number}
                                <span class="phone-number">
                                    {if $contact.call_prefix}<span class="country-prefix">+{$contact.call_prefix}</span> {/if}{$contact.contact_phone_number}
                                </span>
                            {else}No especificado{/if}<br>
                        <strong>WhatsApp:</strong>
                            {if $contact.contact_phone_number_wp}
                                <span class="phone-number">
                                    {if $contact.call_prefix_wp}<span class="country-prefix">+{$contact.call_prefix_wp}</span> {/if}{$contact.contact_phone_number_wp}
                                </span>
                                <a href="https://wa.me/{if $contact.call_prefix_wp}{$contact.call_prefix_wp}{/if}{$contact.contact_phone_number_wp}" target="_blank" class="btn btn-success btn-sm" style="margin-left:8px;">
                                    Enviar WhatsApp
                                </a>

                            {else}No especificado{/if}<br>
                        <strong>Email:</strong> {$contact.contact_email}<br>
                        <strong>Parentesco:</strong> {$contact.relationship}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}

    {if $pet.covid}
        <div class="card mb-4">
            <div class="card-header"><h2 class="h5">Vacunación COVID-19</h2></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Vacunado:</strong> {if $pet.covid.vaccinated}Sí{else}No{/if}</li>
                {if $pet.covid.vaccinated}
                    <li class="list-group-item"><strong>Dosis:</strong> {$pet.covid.doses}</li>
                    <li class="list-group-item"><strong>Última dosis:</strong> {$pet.covid.last_dose_date}</li>
                    {if $pet.covid.notes}
                        <li class="list-group-item"><strong>Notas:</strong> {$pet.covid.notes}</li>
                    {/if}
                {/if}
            </ul>
        </div>
    {/if}

    {if $pet.conditions}
        <div class="card mb-4">
            <div class="card-header"><h2 class="h5">Condiciones Médicas</h2></div>
            <ul class="list-group list-group-flush">
                {foreach from=$pet.conditions item=condition}
                    <li class="list-group-item">
                        <strong>{$condition.condition_name}</strong>{if $condition.note}: {$condition.note}{/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}

    {if $pet.allergies}
        <div class="card mb-4">
            <div class="card-header"><h2 class="h5">Alergias</h2></div>
            <ul class="list-group list-group-flush">
                {foreach from=$pet.allergies item=allergy}
                    <li class="list-group-item">
                        <strong>{$allergy.allergen}</strong>{if $allergy.note}: {$allergy.note}{/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}

    {if $pet.medications}
        <div class="card mb-4">
            <div class="card-header"><h2 class="h5">Medicamentos</h2></div>
            <ul class="list-group list-group-flush">
                {foreach from=$pet.medications item=medication}
                    <li class="list-group-item">
                        <strong>{$medication.med_name}</strong>
                        {if $medication.dose} - Dosis: {$medication.dose}{/if}
                        {if $medication.frequency} - Frecuencia: {$medication.frequency}{/if}
                        {if $medication.note} - Nota: {$medication.note}{/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}

    <div class="mt-4">
        {if $own}
            <a href="{$edit_link}" class="btn btn-warning btn-lg">
                Editar información
            </a>
        {else}
            <p>Enviar un mensaje y compartir tu ubicación con la persona de contacto vía WhatsApp:</p>
            {if $pet.contacts.0.contact_phone_number}
                <a href="https://wa.me/{if $pet.contacts.0.call_prefix}{$pet.contacts.0.call_prefix}{/if}{$pet.contacts.0.contact_phone_number}?text={urlencode("Hola, he encontrado a ")}{urlencode($pet.user_name)}{urlencode(".")}"
                    target="_blank"
                    class="btn btn-success btn-lg">
                    Enviar mensaje
                </a>
            {/if}
        {/if}
    </div>
{else}
    <div class="alert alert-warning">
        No se encontró información para este código o el QR no está activo.
    </div>
{/if}

{/block}