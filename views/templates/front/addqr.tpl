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
            <option value="CC" {if {$qr_data.user_type_dni|default:''|escape:'html'} == "CC"}selected {/if}>Cédula de ciudadanía</option>
            <option value="TI" {if {$qr_data.user_type_dni|default:''|escape:'html'} == "TI"}selected {/if}>Tarjeta de identidad</option>
            <option value="CE" {if {$qr_data.user_type_dni|default:''|escape:'html'} == "CE"}selected {/if}>Cédula de extranjería</option>
        </select>
    </div>

    <div class="form-group">
        <label for="user_dni">Número de Documento *</label>
        <input type="text" name="user_dni" id="user_dni" class="form-control" required
               value="{$qr_data.user_dni|default:''|escape:'html'}">
    </div>

    <div class="form-group">
        <label for="user_birthdate">Fecha de Nacimiento</label>
        <input type="date" name="user_birthdate" id="user_birthdate" class="form-control"
               value="{$qr_data.user_birthdate|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_gender">Género</label>
        <select name="user_gender" id="user_gender" class="form-control">
            <option value="" disabled {if {$qr_data.user_gender|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
            <option value="MASCULINO" {if {$qr_data.user_gender|default:''|escape:'html'} == "MASCULINO"}selected {/if}>Masculino</option>
            <option value="FEMENINO" {if {$qr_data.user_gender|default:''|escape:'html'} == "FEMENINO"}selected {/if}>Femenino</option>
            <option value="OTRO" {if {$qr_data.user_gender|default:''|escape:'html'} == "OTRO"}selected {/if}>Otro</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="user_stature_cm">Estatura (cm)</label>
        <input type="number" name="user_stature_cm" id="user_stature_cm" class="form-control"
               value="{$qr_data.user_stature_cm|default:''|escape:'html'}">
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
        <label for="user_weight_kg">Peso (kg)</label>
        <input type="number" step="0.01" name="user_weight_kg" id="user_weight_kg" class="form-control"
               value="{$qr_data.user_weight_kg|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_has_eps">Tiene EPS</label>
        <select name="user_has_eps" id="user_has_eps" class="form-control">
            <option value="0" {if {$qr_data.user_has_eps|default:0} == 0}selected {/if}>No</option>
            <option value="1" {if {$qr_data.user_has_eps|default:0} == 1}selected {/if}>Sí</option>
        </select>
    </div>
    
    <div id="eps_name" class="form-group" {if {$qr_data.user_has_eps|default:0} == 0}style="display: none;"{/if}>
        <label for="user_eps_name">Nombre de la EPS</label>
        <input type="text" name="user_eps_name" id="user_eps_name" class="form-control"
               value="{$qr_data.user_eps_name|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_has_prepaid">Tiene Prepagada</label>
        <select name="user_has_prepaid" id="user_has_prepaid" class="form-control">
            <option value="0" {if {$qr_data.user_has_prepaid|default:0} == 0}selected {/if}>No</option>
            <option value="1" {if {$qr_data.user_has_prepaid|default:0} == 1}selected {/if}>Sí</option>
        </select>
    </div>
    
    <div id="prepa_name" class="form-group" {if {$qr_data.user_has_prepaid|default:0} == 0}style="display: none;"{/if}>
        <label for="user_prepaid_name">Nombre de la Prepagada</label>
        <input type="text" name="user_prepaid_name" id="user_prepaid_name" class="form-control"
               value="{$qr_data.user_prepaid_name|default:''|escape:'html'}">
    </div>
    
    <div class="form-group">
        <label for="user_blood_type">Tipo de Sangre</label>
        <select name="user_blood_type" id="user_blood_type" class="form-control">
            <option value="" disabled {if {$qr_data.user_blood_type|default:''|escape:'html'} == ""}selected {/if}>Selecciona una opción</option>
            <option value="O+" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "O+"}selected {/if}>O+</option>
            <option value="O-" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "O-"}selected {/if}>O-</option>
            <option value="A+" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "A+"}selected {/if}>A+</option>
            <option value="A-" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "A-"}selected {/if}>A-</option>
            <option value="B+" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "B+"}selected {/if}>B+</option>
            <option value="B-" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "B-"}selected {/if}>B-</option>
            <option value="AB+" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "AB+"}selected {/if}>AB+</option>
            <option value="AB-" {if {$qr_data.user_blood_type|default:''|escape:'html'} == "AB-"}selected {/if}>AB-</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="user_accepts_transfusions">Acepta Transfusiones</label>
        <select name="user_accepts_transfusions" id="user_accepts_transfusions" class="form-control">
            <option value="1" {if {$qr_data.user_accepts_transfusions|default:1} == 1}selected {/if}>Sí</option>
            <option value="0" {if {$qr_data.user_accepts_transfusions|default:1} == 0}selected {/if}>No</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="user_organ_donor">Donador de Órganos</label>
        <select name="user_organ_donor" id="user_organ_donor" class="form-control">
            <option value="0" {if {$qr_data.user_organ_donor|default:0} == 0}selected {/if}>No</option>
            <option value="1" {if {$qr_data.user_organ_donor|default:0} == 1}selected {/if}>Sí</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="extra_notes">Observaciones</label>
        <textarea name="extra_notes" id="extra_notes" class="form-control" rows="3">{$qr_data.extra_notes|default:''|escape:'html'}</textarea>
    </div>

    <hr>
    
    <h2>Información de Contacto de Emergencia</h2> 
    
    <div id="contacts_container">
        <!-- Contacto 1 -->
        <div class="contact-item" data-contact-index="0">
            <h4>Contacto de Emergencia 1</h4>
            <div class="form-group">
                <label for="contact_name_0">Nombre Completo *</label>
                <input type="text" name="contact_name[]" id="contact_name_0" class="form-control" required
                       value="{$qr_data.contacts.0.contact_name|default:$customer->firstname|escape:'html'}">
            </div>

            <div class="form-group">
                <label for="contact_phone_0">Teléfono</label>
                <input type="text" name="contact_phone[]" id="contact_phone_0" class="form-control"
                       value="{$qr_data.contacts.0.contact_phone|default:''|escape:'html'}">
            </div>

            <div class="form-group">
                <label for="contact_email_0">Correo electrónico</label>
                <input type="email" name="contact_email[]" id="contact_email_0" class="form-control"
                       value="{$qr_data.contacts.0.contact_email|default:$customer->email|escape:'html'}">
            </div>

            <div class="form-group">
                <label for="relationship_0">Parentesco</label>
                <input type="text" name="relationship[]" id="relationship_0" class="form-control" 
                       value="{$qr_data.contacts.0.relationship|default:''|escape:'html'}">
            </div>
        </div>

        <!-- Contacto 2 (opcional) -->
        <div id="contact_item_1" class="contact-item" data-contact-index="1" {if !$qr_data.contacts.1}style="display: none;"{/if}>
            <h4>Contacto de Emergencia 2</h4>
            <div class="form-group">
                <label for="contact_name_1">Nombre Completo</label>
                <input type="text" name="contact_name[]" id="contact_name_1" class="form-control"
                    value="{$qr_data.contacts.1.contact_name|default:''|escape:'html'}">
            </div>
            <div class="form-group">
                <label for="contact_phone_1">Teléfono</label>
                <input type="text" name="contact_phone[]" id="contact_phone_1" class="form-control"
                    value="{$qr_data.contacts.1.contact_phone|default:''|escape:'html'}">
            </div>
            <div class="form-group">
                <label for="contact_email_1">Correo electrónico</label>
                <input type="email" name="contact_email[]" id="contact_email_1" class="form-control"
                    value="{$qr_data.contacts.1.contact_email|default:''|escape:'html'}">
            </div>
            <div class="form-group">
                <label for="relationship_1">Parentesco</label>
                <input type="text" name="relationship[]" id="relationship_1" class="form-control" 
                    value="{$qr_data.contacts.1.relationship|default:''|escape:'html'}">
            </div>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <button type="button" id="add_contact" class="btn btn-secondary btn-sm">
            Agregar segundo contacto
        </button>
    </div>

    <hr>
    
    <h2>Información de Vacunación COVID-19</h2>
    
    <div class="form-group">
        <label for="vaccinated">¿Está vacunado contra COVID-19?</label>
        <select name="vaccinated" id="vaccinated" class="form-control">
            <option value="0" {if {$qr_data.covid.vaccinated|default:0} == 0}selected {/if}>No</option>
            <option value="1" {if {$qr_data.covid.vaccinated|default:0} == 1}selected {/if}>Sí</option>
        </select>
    </div>
    
    <div id="covid_details" class="form-group" {if {$qr_data.covid.vaccinated|default:0} == 0}style="display: none;"{/if}>
        <div class="form-group">
            <label for="doses">Número de dosis</label>
            <input type="number" name="doses" id="doses" class="form-control" min="1" max="5"
                   value="{$qr_data.covid.doses|default:''|escape:'html'}">
        </div>
        
        <div class="form-group">
            <label for="last_dose_date">Fecha de última dosis</label>
            <input type="date" name="last_dose_date" id="last_dose_date" class="form-control"
                   value="{$qr_data.covid.last_dose_date|default:''|escape:'html'}">
        </div>
        
        <div class="form-group">
            <label for="covid_notes">Notas sobre vacunación</label>
            <textarea name="covid_notes" id="covid_notes" class="form-control" rows="2">{$qr_data.covid.notes|default:''|escape:'html'}</textarea>
        </div>
    </div>

    <hr>
    
    <h2>Condiciones Médicas</h2>
    
    <div id="conditions_container">
        {if $qr_data.conditions}
            {foreach from=$qr_data.conditions item=condition key=index}
                <div class="condition-item">
                    <div>
                        <label for="condition_{$index}">Condición médica</label>
                        <input type="text" name="conditions[]" id="condition_{$index}" class="form-control" value="{$condition.condition_name|escape:'html'}">
                    </div>
                    <div>
                        <label for="condition_note_{$index}">Nota</label>
                        <input type="text" name="condition_notes[]" id="condition_note_{$index}" class="form-control" value="{$condition.note|escape:'html'}">
                    </div>
                </div>
            {/foreach}
        {else}
            <div class="condition-item">
                <div>
                    <label>Condición médica</label>
                    <input type="text" name="conditions[]" class="form-control">
                </div>
                <div>
                    <label>Nota</label>
                    <input type="text" name="condition_notes[]" class="form-control">
                </div>
            </div>
        {/if}
    </div>

    
    <div class="text-center mt-3">
        <button type="button" id="add_condition" class="btn btn-secondary btn-sm">
            Agregar condición
        </button>
    </div>

    <hr>

    <h2>Alergias</h2>

    <div id="allergies_container">
        {if $qr_data.allergies}
            {foreach from=$qr_data.allergies item=allergy key=index}
                <div class="allergy-item">
                    <div>
                        <label for="allergy_{$index}">Alergia</label>
                        <input type="text" name="allergies[]" id="allergy_{$index}" class="form-control" value="{$allergy.allergen|escape:'html'}">
                    </div>
                    <div>
                        <label for="allergy_note_{$index}">Nota</label>
                        <input type="text" name="allergy_notes[]" id="allergy_note_{$index}" class="form-control" value="{$allergy.note|escape:'html'}">
                    </div>
                </div>
            {/foreach}
        {else}
            <div class="allergy-item">
                <div>
                    <label>Alergia</label>
                    <input type="text" name="allergies[]" class="form-control">
                </div>
                <div>
                    <label>Nota</label>
                    <input type="text" name="allergy_notes[]" class="form-control">
                </div>
            </div>
        {/if}
    </div>

    <div class="text-center mt-3">
        <button type="button" id="add_allergy" class="btn btn-secondary btn-sm">
            Agregar alergia
        </button>
    </div>
    
    <hr>
    
    <h2>Medicamentos</h2>

    <div id="medications_container">
        {if $qr_data.medications}
            {foreach from=$qr_data.medications item=medication key=index}
                <div class="medication-item">
                    <div>
                        <label for="med_{$index}">Medicamento</label>
                        <input type="text" name="medications[]" id="med_{$index}" class="form-control" value="{$medication.med_name|escape:'html'}">
                    </div>
                    <div>
                        <label for="dose_{$index}">Dosis</label>
                        <input type="text" name="med_doses[]" id="dose_{$index}" class="form-control" value="{$medication.dose|escape:'html'}">
                    </div>
                    <div>
                        <label for="freq_{$index}">Frecuencia</label>
                        <input type="text" name="med_frequencies[]" id="freq_{$index}" class="form-control" value="{$medication.frequency|escape:'html'}">
                    </div>
                    <div>
                        <label for="note_{$index}">Nota</label>
                        <input type="text" name="med_notes[]" id="note_{$index}" class="form-control" value="{$medication.note|escape:'html'}">
                    </div>
                </div>
            {/foreach}
        {else}
            <div class="medication-item">
                <div>
                    <label>Medicamento</label>
                    <input type="text" name="medications[]" class="form-control">
                </div>
                <div>
                    <label>Dosis</label>
                    <input type="text" name="med_doses[]" class="form-control">
                </div>
                <div>
                    <label>Frecuencia</label>
                    <input type="text" name="med_frequencies[]" class="form-control">
                </div>
                <div>
                    <label>Nota</label>
                    <input type="text" name="med_notes[]" class="form-control">
                </div>
            </div>
        {/if}
    </div>
    
    <div class="text-center mt-3">
        <button type="button" id="add_medication" class="btn btn-secondary btn-sm">
            Agregar medicamento
        </button>
    </div>
</div>

<hr class="my-4">

<!-- Botones principales con mejor separación -->
<div class="form-actions text-center">
    <button type="submit" name="submit_qr_code" class="btn btn-primary btn-lg px-5">
        {if $edit_mode}
            Guardar cambios
        {else}
            Registrar QR
        {/if}
    </button>
    
    <a href="{$link->getPageLink('module-qrsoldproducts-manageqr-custom')}" class="btn btn-secondary btn-lg px-5 ml-3">
        Cancelar
    </a>
</div>
</form>

<style>
.form-actions {
    background-color: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 2rem;
    border: 1px solid #dee2e6;
}

.form-actions .btn {
    margin: 0 0.5rem;
}

.condition-item,
.allergy-item,
.medication-item {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-bottom: 1rem;
}

.condition-item > div,
.allergy-item > div {
    flex: 1 1 48%;
}

.medication-item > div {
    flex: 1 1 23%;
}

.condition-item label,
.allergy-item label,
.medication-item label {
    font-weight: 500;
    color: #343a40;
}

.contact-item {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.contact-item h4 {
    color: #495057;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar campos de EPS
    const epsSelect = document.getElementById('user_has_eps');
    const epsNameDiv = document.getElementById('eps_name');
    
    epsSelect.addEventListener('change', function() {
        epsNameDiv.style.display = this.value == '1' ? 'block' : 'none';
    });
    
    // Mostrar/ocultar campos de Prepagada
    const prepaidSelect = document.getElementById('user_has_prepaid');
    const prepaidNameDiv = document.getElementById('prepa_name');
    
    prepaidSelect.addEventListener('change', function() {
        prepaidNameDiv.style.display = this.value == '1' ? 'block' : 'none';
    });
    
    // Mostrar/ocultar campos de COVID
    const vaccinatedSelect = document.getElementById('vaccinated');
    const covidDetailsDiv = document.getElementById('covid_details');
    
    vaccinatedSelect.addEventListener('change', function() {
        covidDetailsDiv.style.display = this.value == '1' ? 'block' : 'none';
    });

    // Agregar contacto de emergencia 2
    // Mostrar el segundo contacto si no está visible
    const addContactBtn = document.getElementById('add_contact');
    const contact2 = document.getElementById('contact_item_1');

    addContactBtn.addEventListener('click', function () {
        if (contact2 && contact2.style.display === 'none') {
            contact2.style.display = 'block';
            addContactBtn.style.display = 'none';
        }
    });

    
    // Agregar condición médica
    document.getElementById('add_condition').addEventListener('click', function() {
        const container = document.getElementById('conditions_container');
        const newItem = document.createElement('div');
        newItem.className = 'condition-item form-row mt-2';
        newItem.innerHTML = `
            <div class="col-md-6">
                <label>Condición médica</label>
                <input type="text" name="conditions[]" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Nota</label>
                <input type="text" name="condition_notes[]" class="form-control">
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Agregar alergia
    document.getElementById('add_allergy').addEventListener('click', function() {
        const container = document.getElementById('allergies_container');
        const newItem = document.createElement('div');
        newItem.className = 'allergy-item form-row mt-2';
        newItem.innerHTML = `
            <div class="col-md-6">
                <label>Alergia</label>
                <input type="text" name="allergies[]" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Nota</label>
                <input type="text" name="allergy_notes[]" class="form-control">
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Agregar medicamento
    document.getElementById('add_medication').addEventListener('click', function() {
        const container = document.getElementById('medications_container');
        const newItem = document.createElement('div');
        newItem.className = 'medication-item form-row mt-2';
        newItem.innerHTML = `
            <div class="col-md-3">
                <label>Medicamento</label>
                <input type="text" name="medications[]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Dosis</label>
                <input type="text" name="med_doses[]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Frecuencia</label>
                <input type="text" name="med_frequencies[]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Nota</label>
                <input type="text" name="med_notes[]" class="form-control">
            </div>
        `;
        container.appendChild(newItem);
    });
});
</script>

{/block}