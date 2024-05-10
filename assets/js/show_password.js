function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icons = document.querySelectorAll(".icon_password");

    if (field.type === "password") {
        field.type = "text";
        icons.forEach(icon => {
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        });
    } else {
        field.type = "password";
        icons.forEach(icon => {
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        });
    }
}

function togglePassword() {
    togglePasswordVisibility("password");
}

function togglecPassword() {
    togglePasswordVisibility("confirm_password");
}