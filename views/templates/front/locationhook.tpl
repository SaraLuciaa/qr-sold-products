{literal}
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            fetch('/module/qrsoldproducts/locationhook', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qr_code: '{/literal}{$code}{literal}', // ✅ corregido
                    lat: position.coords.latitude,
                    lon: position.coords.longitude
                })
            });
        });
    }
});
</script>
{/literal}
