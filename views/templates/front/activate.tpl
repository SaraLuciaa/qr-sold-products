{extends file='page.tpl'}

{block name='page_title'}QR Personas{/block}

{block name='page_content'}
    {include file='module:qrsoldproducts/views/templates/front/locationhook.tpl'}


    {if $pet}
        <h2>Información Personal</h2>
        <ul>
            <li><strong>Nombre Completo:</strong> {$pet.user_name}</li>
            <li><strong>Tipo de documento:</strong> {$pet.user_type_dni}</li>
            <li><strong>Numero de Documento:</strong> {$pet.user_dni}</li>
            <li><strong>Fecha de Nacimiento:</strong> {$pet.user_Birthdate}</li>
            <li><strong>Genero:</strong> {$pet.user_gender}</li>
            <li><strong>Estatura:</strong> {$pet.user_stature}</li>
            <li><strong>Dirección:</strong> {$pet.user_address}</li>
            <li><strong>Teléfono celular:</strong> {$pet.user_phone_mobile}</li>
            <li><strong>Teléfono residencial:</strong> {$pet.user_phone_home}</li>
            <li><strong>Teléfono del trabajo:</strong> {$pet.user_phone_work}</li>
            <li><strong>Peso:</strong> {$pet.user_weight}</li>
            {if $pet.user_eps == "Si"}
            <li><strong>EPS:</strong> {$pet.user_eps_name}</li>
            {else}
            <li><strong>EPS:</strong> No tiene</li>
            {/if}
            {if $pet.user_prepaid == "Si"}
            <li><strong>Prepagada:</strong> {$pet.user_prepaid_name}</li>
            {else}
            <li><strong>Prepagada:</strong> No tiene</li>
            {/if}
            <li><strong>Tipo de Sangre:</strong> {$pet.user_blood_type}</li>
            <li><strong>Donador de Organos:</strong> {$pet.user_donor}</li>
            <li><strong>Vacuna contra el Covid:</strong> {$pet.user_covid}</li>
            <li><strong>Enfermedades:</strong> {$pet.user_diseases}</li>
            <li><strong>Observaciones:</strong> {$pet.extra_notes}</li>
            <li><strong>Notas médicas:</strong> {$pet.medical_info}</li>
        </ul>    
        <h2>Información de persona de Contacto</h2>    
        <ul>    
            <li><strong>Nombre Completo:</strong> {$pet.owner_name}</li>
            <li><strong>Teléfono:</strong> {$pet.owner_phone}</li>
            <li><strong>Correo electrónico:</strong> {$pet.owner_email}</li>
            <li><strong>Parentezco:</strong> {$pet.owner_relationship}</li>
        </ul>

        <div class="mt-4 d-flex flex-wrap gap-2">
            {if $own}
                <a href="{$edit_link}" class="btn btn-warning px-4">
                    Editar información
                </a>
            {else}
                <p class="nota-enm">Enviar un mensaje y compartir tu ubicación con la persona de contacto vía WhatsApp  </p>
                <a href="https://wa.me/57{$pet.owner_phone}?text={urlencode('Hola, he encontrado a tu mascota.')}" 
                target="_blank" 
                class="btn btn-success d-flex align-items-center px-4">
                    Enviar mensaje
                </a>
            {/if}
        </div>
    {else}
        <p>No se encontró información para este código o el QR no está activo.</p>
    {/if}
{/block}