

  
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

//cart 

function fetchCartCount() {
    fetch('fetch_cart_count.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('cart-count').innerText = data.cart_count;
    })
    .catch(error => console.error('Error fetching cart count:', error));
}

// Fetch cart count when the page loads
document.addEventListener("DOMContentLoaded", fetchCartCount);
function updateCartCount(count) {
    document.getElementById('cart-count').innerText = count;
}

        function confirmLogout() {
         var logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));
         logoutModal.show();
            }