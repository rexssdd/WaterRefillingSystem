

// signup scriptss
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("signupForm");
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const contact = document.getElementById("contact");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");

    const errors = {
        username: document.getElementById("usernameError"),
        email: document.getElementById("emailError"),
        contact: document.getElementById("contactError"),
        password: document.getElementById("passwordError"),
        confirmPassword: document.getElementById("confirmPasswordError")
    };

    function hideError(field) {
        errors[field].textContent = "";
        errors[field].style.display = "none";
    }

    function showError(field, message) {
        errors[field].textContent = message;
        errors[field].style.display = "block";
    }

    contact.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, ""); // Allow only numbers
        if (/^\d{11}$/.test(this.value)) hideError("contact");
    });

    email.addEventListener("input", function () {
        if (validateGmail(this.value)) hideError("email");
    });

    password.addEventListener("input", function () {
        if (this.value.length >= 6) hideError("password");
    });

    confirmPassword.addEventListener("input", function () {
        if (password.value === confirmPassword.value) hideError("confirmPassword");
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        let valid = true;

        Object.values(errors).forEach(error => hideError(error));

        if (username.value.trim() === "") {
            showError("username", "Username is required");
            valid = false;
        }

        if (!validateGmail(email.value)) {
            showError("email", "Email must be a valid @gmail.com address");
            valid = false;
        }

        if (!/^\d{11}$/.test(contact.value)) {
            showError("contact", "Contact number must be exactly 11 digits");
            valid = false;
        }

        if (password.value.length < 6) {
            showError("password", "Password must be at least 6 characters");
            valid = false;
        }

        if (password.value !== confirmPassword.value) {
            showError("confirmPassword", "Passwords do not match!");
            valid = false;
        }

        if (valid) {
            alert("Signup successful!");
            form.submit();
        }
    });

    function validateGmail(email) {
        return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
    }
});

const formOpenBtn = document.querySelector("#form-open"),
home = document.querySelector(".home"),
formContainer = document.querySelector(".form_container"),
formCloseBtn = document.querySelector(".form_close"),
signupBtn = document.querySelector("#signup"),
loginBtn = document.querySelector("#login"),
pwShowHide = document.querySelectorAll(".pw_hide");

formOpenBtn.addEventListener("click", ()=> home.classList.add("show"));
formCloseBtn.addEventListener("click", ()=> home.classList.remove("show"));

pwShowHide.forEach((icon) => {
    icon.addEventListener("click", () => {
        let getPwInput = icon.parentElement.querySelector("input");
       if(getPwInput.type === "password"){
            getPwInput.type = "text";
            icon.classList.replace("uil-eye-slash","uil-eye");
       } else{
        getPwInput.type = "password";
            icon.classList.replace("uil-eye", "uil-eye-slash");
       }
    });
});
signupBtn.addEventListener("click", (e)=> {
    e.preventDefault();
    formContainer.classList.add("active");
});
loginBtn.addEventListener("click", (e)=> {
    e.preventDefault();
    formContainer.classList.remove("active");
});

