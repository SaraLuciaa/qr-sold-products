{extends file='page.tpl'}

{block name='page_title'}
    {if $edit_mode}
        Editar información de QR 
    {else}
        Registrar QR personal
    {/if}
{/block}

{block name='page_content'}

{if $error}
    <div class="alert alert-danger">{$error|escape:'html'}</div>
{/if}
{if $success}
    <div class="alert alert-success">{$success|escape:'html'}</div>
{/if}

<form method="post" class="form-horizontal">
    {if !$edit_mode}
        <div class="form-group">
            <label for="validation_code">Código de Validación *</label>
            <input type="text" name="validation_code" id="validation_code" class="form-control" required>
        </div>
    {else}
        <div class="form-group">
            <label>Código de Validación</label>
            <input type="text" class="form-control" value="{$qr_data.validation_code}" disabled>
        </div>
    {/if}

    <hr>

    <h2>Información Personal</h2>
    
    <div class="form-group">
        <label for="user_name">Nombre Completo *</label>
        <input type="text" name="user_name" id="user_name" class="form-control" required
               value="{$qr_data.user_name|default:''|escape:'html'}">
    </div>

    <div class="form-group">
        <label for="user_type_dni">Tipo de Documento *</label>
        
        <select name="user_type_dni" id="user_type_dni" class="form-control" required>
          <option value="" disabled {if {$qr_data.user_type_dni|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
          <option value="cedula_ciudadania" {if {$qr_data.user_type_dni|default:''|escape:'html'} == "cedula_ciudadania"}selected {/if}>Cédula de ciudadanía</option>
          <option value="tarjeta_identidad" {if {$qr_data.user_type_dni|default:''|escape:'html'} == "tarjeta_identidad"}selected {/if}>Tarjeta de identidad</option>
          <option value="cedula_extranjera" {if {$qr_data.user_type_dni|default:''|escape:'html'} == "cedula_extranjera"}selected {/if}>Cédula de extranjería</option>
        </select>
    </div>

    <div class="form-group">
        <label for="user_dni">Numero de Documento *</label>
        <input type="text" name="user_dni" id="user_dni" class="form-control"
               value="{$qr_data.user_dni|default:''|escape:'html'}" required>
    </div>

    <div class="form-group">
        <label for="user_Birthdate">Fecha de Nacimiento</label>
        <input type="date" name="user_Birthdate" id="user_Birthdate" class="form-control"
               value="{$qr_data.user_Birthdate|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_gender">Genero</label>
        <input type="text" name="user_gender" id="user_gender" class="form-control"
               value="{$qr_data.user_gender|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_stature">Estatura</label>
        <input type="text" name="user_stature" id="user_stature" class="form-control"
               value="{$qr_data.user_stature|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_address">Dirección</label>
        <input type="text" name="user_address" id="user_address" class="form-control"
               value="{$qr_data.user_address|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_phone_mobile">Teléfono móvil</label>
        <input type="text" name="user_phone_mobile" id="user_phone_mobile" class="form-control"
               value="{$qr_data.user_phone_mobile|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_phone_home">Teléfono residencial</label>
        <input type="text" name="user_phone_home" id="user_phone_home" class="form-control"
               value="{$qr_data.user_phone_home|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_phone_work">Teléfono del trabajo</label>
        <input type="text" name="user_phone_work" id="user_phone_work" class="form-control"
               value="{$qr_data.user_phone_work|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_weight">Peso</label>
        <input type="text" name="user_weight" id="user_weight" class="form-control"
               value="{$qr_data.user_weight|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_eps">Tiene EPS</label>
               
        <select name="user_eps" id="user_eps" class="form-control">
          <option value="" disabled {if {$qr_data.user_eps|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
          
          <option value="Si" {if {$qr_data.user_eps|default:''|escape:'html'} == "SI"}selected {/if}>Si</option>
          <option value="No" {if {$qr_data.user_eps|default:''|escape:'html'} == "No"}selected {/if}>No</option>
        </select>
               
    </div>
    
    {if {$qr_data.user_eps|default:''|escape:'html'} == "Si"}
    <div id="eps_name" class="form-group">
        <label for="user_eps_name">Nombre de la EPS</label>
        <input type="text" name="user_eps_name" id="user_eps_name" class="form-control"
               value="{$qr_data.user_eps_name|default:''|escape:'html'}">
    </div>
    {else}
       <div id="eps_name" class="form-group" style="display: none;">
        <label for="user_eps_name">Nombre de la EPS</label>
        <input type="text" name="user_eps_name" id="user_eps_name" class="form-control"
               value="{$qr_data.user_eps_name|default:''|escape:'html'}">
    </div>
       
    {/if}
    
    <div class="form-group">
        <label for="user_prepaid">Tiene Prepagada</label>
               
        <select name="user_prepaid" id="user_prepaid" class="form-control">
          <option value="" disabled {if {$qr_data.user_prepaid|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
          
          <option value="Si" {if {$qr_data.user_prepaid|default:''|escape:'html'} == "Si"}selected {/if}>Si</option>
          <option value="No" {if {$qr_data.user_prepaid|default:''|escape:'html'} == "No"}selected {/if}>No</option>
        </select>
               
    </div>
    
    {if {$qr_data.user_prepaid|default:''|escape:'html'} == "Si"}
    <div id="prepa_name" class="form-group">
        <label for="user_prepaid_name">Nombre de la Prepagada</label>
        <input type="text" name="user_prepaid_name" id="user_prepaid_name" class="form-control"
               value="{$qr_data.user_prepaid_name|default:''|escape:'html'}">
    </div>
    {else}
    <div id="prepa_name" class="form-group" style="display: none;">
        <label for="user_prepaid_name">Nombre de la Prepagada</label>
        <input type="text" name="user_prepaid_name" id="user_prepaid_name" class="form-control"
               value="{$qr_data.user_prepaid_name|default:''|escape:'html'}">
    </div>
    {/if}
    
    
    <div class="form-group">
        <label for="user_blood_type">Tipo de Sangre</label>
               
        <select name="user_blood_type" id="user_blood_type" class="form-control">
          <option value="" disabled {if {$qr_data.user_blood_type|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
          
          <option value="O_positivo" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "O_positivo"}selected {/if}>O Positivo</option>
          <option value="B_negativo" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "B_negativo"}selected {/if}>B negativo</option>
        </select>
               
    </div>
    
    <div class="form-group">
        <label for="user_donor">Donador de Organos</label>
               
        <select name="user_donor" id="user_donor" class="form-control">
          <option value="" disabled {if {$qr_data.user_donor|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
          
          <option value="Si" {if {$qr_data.user_donor|default:''|escape:'html'} == "Si"}selected {/if}>Si</option>
          <option value="No" {if {$qr_data.user_donor|default:''|escape:'html'} == "No"}selected {/if}>No</option>
        </select>
               
    </div>
    
    <div class="form-group">
        <label for="user_covid">Vacuna contra el COVID</label>
               
        <select name="user_covid" id="user_covid" class="form-control">
          <option value="" disabled {if {$qr_data.user_covid|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
          
          <option value="Si" {if {$qr_data.user_covid|default:''|escape:'html'} == "SI"}selected {/if}>Si</option>
          <option value="No" {if {$qr_data.user_covid|default:''|escape:'html'} == "No"}selected {/if}>No</option>
        </select>
               
    </div>
    
    <div class="form-group">
        <label for="user_diseases">Enfermedades</label>
        <input type="text" name="user_diseases" id="user_diseases" class="form-control"
               value="{$qr_data.user_diseases|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="medical_info">Notas Médicas</label>
        <input type="text" name="medical_info" id="medical_info" class="form-control"
               value="{$qr_data.medical_info|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="extra_notes">Observaciones</label>
        <input type="text" name="extra_notes" id="extra_notes" class="form-control"
               value="{$qr_data.extra_notes|default:''|escape:'html'}">
    </div>

    <hr>
    
    <h2>Información de la persona de contacto</h2> 
    
    <div class="form-group">
        <label for="owner_name">Nombre Completo *</label>
        <input type="text" name="owner_name" id="owner_name" class="form-control" required
               value="{$qr_data.owner_name|default:$customer->firstname|escape:'html'}">
    </div>

    <div class="form-group">
        <label for="owner_phone">Teléfono móvil *</label>
        <input type="text" name="owner_phone" id="owner_phone" class="form-control" required
               value="{$qr_data.owner_phone|default:''|escape:'html'}">
    </div>

    <div class="form-group">
        <label for="owner_email">Correo electrónico</label>
        <input type="email" name="owner_email" id="owner_email" class="form-control"
               value="{$qr_data.owner_email|default:$customer->email|escape:'html'}">
    </div>

    <div class="form-group">
        <label for="owner_relationship">Parentezco</label>
        <input name="owner_relationship" id="owner_relationship" class="form-control" value="{$qr_data.owner_relationship|default:''|escape:'html'}">
    </div>

    <button type="submit" name="submit_qr_code" class="btn btn-primary mt-3">
        {if $edit_mode}
            Guardar cambios
        {else}
            Registrar QR
        {/if}
    </button>
</form>

{/block}