@if(session('impersonating'))
    <div class="alert alert-warning alert-dismissible fade show sticky-top" style="margin: 0; border-radius: 0; z-index: 9999;">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-user-secret"></i>
                    <strong>IMPERSONATION MODE:</strong>
                    You are impersonating <strong>{{ session('impersonating')['user_name'] }}</strong>
                    as admin <strong>{{ session('impersonating')['admin_name'] }}</strong>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="stopImpersonation()">
                    <i class="fas fa-times"></i> Stop Impersonation
                </button>
            </div>
        </div>
    </div>

    <script>
    function stopImpersonation() {
        // Use Laravel's CSRF token directly
        const csrfToken = '{{ csrf_token() }}';

        fetch('/admin/stop-impersonation', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert('Failed to stop impersonation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while stopping impersonation. Error: ' + error.message);
        });
    }
    </script>
@endif