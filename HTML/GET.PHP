<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">User Authentication</h2>
    
    <!-- Registration Form -->
    <form action="register1.php" method="POST" class="mt-4">
        <h4>Register</h4>
        <input type="text" name="register_username" class="form-control" placeholder="Username" required>
        <input type="email" name="register_email" class="form-control mt-2" placeholder="Email" required>
        <input type="text" name="register_contact" class="form-control mt-2" placeholder="Contact Number" required>
        <input type="password" name="register_password" class="form-control mt-2" placeholder="Password" required>
        <input type="password" name="register_confirm_password" class="form-control mt-2" placeholder="Confirm Password" required>
        <button type="submit" name="register_submit" class="btn btn-primary mt-3">Register</button>
    </form>

    <!-- Login Form -->
    <form action="register1.php" method="POST" class="mt-4">
        <h4>Login</h4>
        <input type="text" name="login_email_or_username" class="form-control" placeholder="Email or Username" required>
        <input type="password" name="login_password" class="form-control mt-2" placeholder="Password" required>
        <button type="submit" name="login_submit" class="btn btn-success mt-3">Login</button>
    </form>
</div>

            <!-- Toast Notification Container -->
            <div class="position-fixed top-0 start-0 p-3 ms-3" style="z-index: 1050; margin-top: 80px;">
            <div id="formToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: rgb(255, 0, 0) !important; color: white !important;">
                <div class="d-flex">
                    <div class="toast-body" id="toastMessage"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if (isset($_SESSION['toast_message'])): ?>
        var toastMessage = <?php echo json_encode($_SESSION['toast_message']); ?>;
        var toastEl = document.getElementById("formToast");
        var toastBody = document.getElementById("toastMessage");

        // Add appropriate background color based on status
        toastEl.classList.add(toastMessage.status === "success" ? "bg-success" : "bg-danger");

        toastBody.textContent = toastMessage.message;

        // Show the toast using Bootstrap's API
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 }); // Auto-hide after 4 seconds
        toast.show();

        <?php unset($_SESSION['toast_message']); ?> // Clear session after displaying the toast
    <?php endif; ?>
});
</script>

</body>
</html>
