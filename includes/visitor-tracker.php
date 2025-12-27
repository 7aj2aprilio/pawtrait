<!-- Visitor Tracking Script -->
<script>
// Track visitor on page load
document.addEventListener('DOMContentLoaded', function() {
    // Send tracking request to API
    fetch('/api/visitor-tracking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        // Silently track - no action needed
        console.log('Visitor tracked');
    })
    .catch(error => {
        // Fail silently
        console.error('Tracking failed:', error);
    });
});
</script>
