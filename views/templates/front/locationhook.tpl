{literal}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Solo enviar ubicación si NO es el dueño del QR
    var isOwner = {/literal}{if $own}true{else}false{/if}{literal};
    
    if (!isOwner && navigator.geolocation) {
        // Mostrar notificación de que se está enviando la ubicación
        var notification = document.getElementById('location-notification');
        if (notification) {
            notification.style.display = 'block';
        }
        
        navigator.geolocation.getCurrentPosition(function (position) {
            fetch('/module/qrsoldproducts/locationhook', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qr_code: '{/literal}{$code}{literal}', 
                    lat: position.coords.latitude,
                    lon: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                if (notification) {
                    if (data.status) {
                        notification.className = 'alert alert-success';
                        notification.innerHTML = '<i class="fa fa-check-circle" aria-hidden="true"></i> <strong>¡Ubicación enviada!</strong> ' + data.status;
                    } else if (data.error) {
                        notification.className = 'alert alert-warning';
                        notification.innerHTML = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>Aviso:</strong> ' + data.error;
                    }
                }
            })
            .catch(error => {
                console.log('Error enviando ubicación:', error);
                if (notification) {
                    notification.className = 'alert alert-warning';
                    notification.innerHTML = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>Aviso:</strong> No se pudo enviar la ubicación a los contactos de emergencia.';
                }
            });
        }, function(error) {
            console.log('Error obteniendo ubicación:', error);
            var notification = document.getElementById('location-notification');
            if (notification) {
                notification.className = 'alert alert-warning';
                notification.innerHTML = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>Aviso:</strong> No se pudo obtener la ubicación.';
                notification.style.display = 'block';
            }
        });
    }
});
</script>
{/literal}