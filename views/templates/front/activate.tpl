{extends file='page.tpl'}

{block name='page_title'}QR Personas{/block}

{block name='page_content'}
    {include file='module:qrsoldproducts/views/templates/front/locationhook.tpl'}

    {if isset($error_message)}
        <div class="alert alert-danger">
            {$error_message}
        </div>
    {/if}

    {if $pet}
        <h2>Información Personal</h2>
        <ul>
            <li><strong>Nombre Completo:</strong> {$pet.user_name}</li>
            <li><strong>Tipo de documento:</strong> 
                {if $pet.user_type_dni == "CC"}Cédula de ciudadanía{/if}
                {if $pet.user_type_dni == "TI"}Tarjeta de identidad{/if}
                {if $pet.user_type_dni == "CE"}Cédula de extranjería{/if}
            </li>
            <li><strong>Número de Documento:</strong> {$pet.user_dni}</li>
            <li><strong>Fecha de Nacimiento:</strong> {$pet.user_birthdate}</li>
            <li><strong>Género:</strong> {$pet.user_gender}</li>
            <li><strong>Estatura:</strong> {$pet.user_stature_cm} cm</li>
            <li><strong>Dirección:</strong> {$pet.user_address}</li>
            <li><strong>Teléfono celular:</strong> {$pet.user_phone_mobile}</li>
            <li><strong>Teléfono residencial:</strong> {$pet.user_phone_home}</li>
            <li><strong>Teléfono del trabajo:</strong> {$pet.user_phone_work}</li>
            <li><strong>WhatsApp:</strong> {$pet.user_whatsapp_e164}</li>
            <li><strong>Peso:</strong> {$pet.user_weight_kg} kg</li>
            
            <li><strong>EPS:</strong> 
                {if $pet.user_has_eps == 1}
                    {$pet.user_eps_name}
                {else}
                    No tiene
                {/if}
            </li>
            
            <li><strong>Prepagada:</strong> 
                {if $pet.user_has_prepaid == 1}
                    {$pet.user_prepaid_name}
                {else}
                    No tiene
                {/if}
            </li>
            
            <li><strong>Tipo de Sangre:</strong> {$pet.user_blood_type}</li>
            <li><strong>Acepta Transfusiones:</strong> 
                {if $pet.user_accepts_transfusions == 1}Sí{else}No{/if}
            </li>
            <li><strong>Donador de Órganos:</strong> 
                {if $pet.user_organ_donor == 1}Sí{else}No{/if}
            </li>
            <li><strong>Observaciones:</strong> {$pet.extra_notes}</li>
        </ul>

        {if $pet.contacts}
            <h2>Información de Contacto de Emergencia</h2>    
            <ul>    
                {foreach from=$pet.contacts item=contact}
                    <li><strong>Nombre Completo:</strong> {$contact.contact_name}</li>
                    <li><strong>Teléfono:</strong> {$contact.contact_phone}</li>
                    <li><strong>WhatsApp:</strong> {$contact.contact_whatsapp_e164}</li>
                    <li><strong>Correo electrónico:</strong> {$contact.contact_email}</li>
                    <li><strong>Parentesco:</strong> {$contact.relationship}</li>
                {/foreach}
            </ul>
        {/if}

        {if $pet.covid}
            <h2>Información de Vacunación COVID-19</h2>
            <ul>
                <li><strong>Vacunado contra COVID-19:</strong> 
                    {if $pet.covid.vaccinated == 1}Sí{else}No{/if}
                </li>
                {if $pet.covid.vaccinated == 1}
                    <li><strong>Número de dosis:</strong> {$pet.covid.doses}</li>
                    <li><strong>Fecha de última dosis:</strong> {$pet.covid.last_dose_date}</li>
                    {if $pet.covid.notes}
                        <li><strong>Notas:</strong> {$pet.covid.notes}</li>
                    {/if}
                {/if}
            </ul>
        {/if}

        {if $pet.conditions}
            <h2>Condiciones Médicas</h2>
            <ul>
                {foreach from=$pet.conditions item=condition}
                    <li><strong>{$condition.condition_name}</strong>
                        {if $condition.note}: {$condition.note}{/if}
                    </li>
                {/foreach}
            </ul>
        {/if}

        {if $pet.allergies}
            <h2>Alergias</h2>
            <ul>
                {foreach from=$pet.allergies item=allergy}
                    <li><strong>{$allergy.allergen}</strong>
                        {if $allergy.note}: {$allergy.note}{/if}
                    </li>
                {/foreach}
            </ul>
        {/if}

        {if $pet.medications}
            <h2>Medicamentos</h2>
            <ul>
                {foreach from=$pet.medications item=medication}
                    <li><strong>{$medication.med_name}</strong>
                        {if $medication.dose} - Dosis: {$medication.dose}{/if}
                        {if $medication.frequency} - Frecuencia: {$medication.frequency}{/if}
                        {if $medication.note} - Nota: {$medication.note}{/if}
                    </li>
                {/foreach}
            </ul>
        {/if}

        <div class="mt-4 d-flex flex-wrap gap-2">
            {if $own}
                <a href="{$edit_link}" class="btn btn-warning px-4">
                    Editar información
                </a>
            {else}
                <p class="nota-enm">Enviar un mensaje y compartir tu ubicación con la persona de contacto vía WhatsApp</p>
                {if $pet.contacts.0.contact_whatsapp_e164}
                    <a href="https://wa.me/{$pet.contacts.0.contact_whatsapp_e164}?text={urlencode('Hola, he encontrado a esta persona.')}" 
                    target="_blank" 
                    class="btn btn-success d-flex align-items-center px-4">
                        Enviar mensaje
                    </a>
                {elseif $pet.contacts.0.contact_phone}
                    <a href="https://wa.me/57{$pet.contacts.0.contact_phone}?text={urlencode('Hola, he encontrado a esta persona.')}" 
                    target="_blank" 
                    class="btn btn-success d-flex align-items-center px-4">
                        Enviar mensaje
                    </a>
                {/if}
            {/if}
        </div>
    {else}
        <p>No se encontró información para este código o el QR no está activo.</p>
    {/if}
{/block}