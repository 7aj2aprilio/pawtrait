// Midtrans Payment Integration
const payButton = document.getElementById('pay-button');

if (payButton) {
    payButton.addEventListener('click', async () => {
        payButton.disabled = true;
        payButton.innerHTML = '<span class="loading"></span> Processing...';

        try {
            // Create transaction
            const response = await fetch('api/create-transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    total: window.checkoutData.total,
                    items: window.checkoutData.items,
                    customer: window.checkoutData.customer
                })
            });

            const result = await response.json();

            if (result.success && result.snap_token) {
                // Open Midtrans Snap popup
                window.snap.pay(result.snap_token, {
                    onSuccess: async function (result) {
                        console.log('Payment success:', result);
                        // Force update status on localhost
                        await fetch('api/update-status.php');
                        window.location.href = 'payment-success.php?order_id=' + result.order_id;
                    },
                    onPending: async function (result) {
                        console.log('Payment pending:', result);
                        // Force update status on localhost
                        await fetch('api/update-status.php');
                        window.location.href = 'payment-pending.php?order_id=' + result.order_id;
                    },
                    onError: function (result) {
                        console.log('Payment error:', result);
                        window.location.href = 'payment-failed.php';
                    },
                    onClose: async function () {
                        console.log('Payment popup closed');
                        // Also check on close just in case
                        await fetch('api/update-status.php');
                        payButton.disabled = false;
                        payButton.innerHTML = 'Pay with Midtrans';
                    }
                });
            } else {
                console.error('Transaction creation failed:', result);
                console.error('Error details:', {
                    http_code: result.http_code,
                    error: result.error,
                    raw_response: result.raw_response,
                    debug_info: result.debug_info
                });
                alert('Failed to create transaction: ' + (result.message || 'Unknown error') + '\n\nCheck browser console for details.');
                payButton.disabled = false;
                payButton.innerHTML = 'Pay with Midtrans';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            payButton.disabled = false;
            payButton.innerHTML = 'Pay with Midtrans';
        }
    });
}
