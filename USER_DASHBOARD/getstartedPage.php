
<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wrsystem";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $_SESSION['toast_message'] = ["status" => "error", "message" => "Database connection failed!"];
    header("Location: getstartedPage.php");
    exit();
}

// Handle Login
if (isset($_POST['login_submit'])) {
    $login_input = trim($_POST['login_email_or_username']);
    $password = $_POST['login_password'];

    if (empty($login_input) || empty($password)) {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "Please enter both username/email and password."];
        header("Location: getstartedPage.php");
        exit();
    }

    // Check if input is an email or username
    if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT user_id, username, password FROM user WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password FROM user WHERE username = ?");
    }

    $stmt->bind_param("s", $login_input);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user found, verify password
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user details in session
            $_SESSION['user_id'] = $user['user_id'];  // Store user ID
            $_SESSION['username'] = $user['username'];
            $_SESSION['toast_message'] = ["status" => "success", "message" => "Login successful!"];

            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['toast_message'] = ["status" => "error", "message" => "Invalid password."];
        }
    } else {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "User not found."];
    }

    header("Location: getstartedPage.php");
    exit();
}

// Fetch all available products categorized
$normal_products = [];
$refill_products = [];
$rental_products = [];

$product_query = "SELECT * FROM product";
$product_result = $conn->query($product_query);

while ($product = $product_result->fetch_assoc()) {
    if ($product['product_type'] == 'normal') {
        $normal_products[] = $product;
    } elseif ($product['product_type'] == 'refill') {
        $refill_products[] = $product;
    } elseif ($product['product_type'] == 'renting') {
        $rental_products[] = $product;
    }
}
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv=" X-UA-Compatible" content = "IE-edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jz Waters Login & Registration Form</title>
    <link   rel = "stylesheet"  href = "/css/style1.css"/>
    <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
    <body>
        <header class="header">
        <nav class="nav">
            <a href="/AdminPage/adminlogin.php" class="nav_logo">
                <img src="/images/Jz.png"  alt="Jz Waters Logo" class="logo">
                Jz Waters
            </a>
            

            <ul class = "nav_items">
                <li class = "nav_item">
                    <a href="#home" class="nav_link">Home</a>
                    <a href="#products" class="nav_link">Products</a>
                    <a href="#contactus" class="nav_link">Contact Us</a>
                    <a href="#developers" class="nav_link">Organizational Structure</a>
                </li>
            </ul>
            <button class = "button" id = "form-open">Login</button>
        </nav>
        </header>
    
                <!-- Toast Notification Container -->
                <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1050; margin-top: 80px;">
                <div id="formToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                    style="background-color: rgb(255, 0, 0) !important; color: white !important; min-width: 200px; max-width: 500px; width: auto;">
                    <div class="d-flex">
                        <div class="toast-body" id="toastMessage"></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>


                <section class="home" id="home">
                    <!-- Notification Popup -->
                <div id="notification" class="notification"></div>
                    <div class="form_container" >
                        <i class="uil uil-times form_close"></i>
          
                 <!-- Login Form -->

                <div class="form login_form">
                    <form action="" method="POST" id="loginForm">
                        <h2>Login</h2>

                        <div class="input_box">
                            <input type="text"  name="login_email_or_username" placeholder="Username or Email" style="size: 8px;" required maxlength="30" />
                            <i class="uil uil-user username"></i>
                        </div>

                        <div class="input_box">
                            <input type="password" name="login_password" placeholder="Enter your password" required />
                            <i class="uil uil-lock password"></i>
                            <i class="uil uil-eye-slash pw_hide"></i>
                        </div>

                        <div class="option_field">
                            <span class="checkbox">
                                <input type="checkbox" id="check">
                                <label for="check">Remember me</label>
                            </span> 
                            <a href="#" class="forgot_pw">Forgot password?</a>
                        </div>

                        <button type="submit" name="login_submit" class="button" id="loginSubmit">Login now</button>
                        <div class="login_signup">Don't have an account? <a href="#" id="signup">Sign Up</a></div>
                    </form>
                </div>

              <!-- Registration Form -->
                <div class="form signup_form">
                    <form id="signupForm">
                        <h2>Sign up</h2>
                        <div class="input_box">
                            <input type="text" name="register_username" id="username" placeholder="Enter your username" required />
                            <i class="uil uil-user username"></i>
                            <div id="usernameError" class="error"></div>
                        </div>
                        <div class="input_box">
                            <input type="email" name="register_email" id="email" placeholder="Enter your email" required />
                            <i class="uil uil-envelope-alt email"></i>
                            <div id="emailError" class="error"></div>
                        </div>
                        <div class="input_box">
                            <input type="text" name="register_contact" id="contact" placeholder="Enter your contact number" required maxlength="11" />
                            <i class="uil uil-phone contact"></i>
                            <div id="contactError" class="error"></div>
                        </div>
                        <div class="input_box">
                            <input type="password" name="register_password" id="password" placeholder="Create password" required />
                            <i class="uil uil-lock password"></i>
                             <i class="uil uil-eye-slash pw_hide"></i>
                            <div id="passwordError" class="error"></div>
                        </div>
                        <div class="input_box">
                            <input type="password" name="register_confirm_password" id="confirmPassword" placeholder="Confirm password" required />
                            <i class="uil uil-lock password"></i>
                             <i class="uil uil-eye-slash pw_hide"></i>
                            <div id="confirmPasswordError" class="error"></div>
                        </div>
                        <button type="submit" class="button" id="signupBtn">Sign up now</button>
                        <div class="login_signup">Already have an account? <a href="#" id="login">Login now</a></div>
                        <p id="responseMessage" style=" size:auto; color:red; max-width: 300px; text-align: center;"></p>
                    </form>
                </div>
            </div>

            <section class="about-us">
                <div class="overlay"></div>
                <div class="about-content">
                    <h2>About us</h2>
                    <p>
                        Welcome to <strong>JZ Water Refilling Station</strong>, your trusted provider of clean, safe, and high-quality drinking water. 
                        We are committed to delivering purified, mineral, and alkaline water that meets the highest industry standards, ensuring 
                        the health and well-being of our customers.
                    </p>
                <div></div>
                    <p>
                        At <strong>JZ Water</strong>, we use advanced filtration and purification technology, including reverse osmosis, UV sterilization, 
                        and multi-stage filtration, to guarantee that every drop is fresh, pure, and free from contaminants.
                    </p>    
                    <div class="buttons">
                        <button type="submit" class="uil uil-clipboard-notes button-order-now" >Order Now</button>
                        <a href="#contactus"  class="uil uil-phone button-contact-us">Contact Us</a>
                    </div>
                </div>
                <img src="/images/Jz.png" alt="JZ Waters Logo" class="about-logo">
            </section>
        
        
                        <section class="water-refill" id="products">
                        <h2>Water Refills</h2>
            <div class="product-container">
                <?php foreach ($refill_products as $product): ?>
                    <?php include '/xampp/htdocs/WaterRefillingSystem/php/product_card.php'; ?>
                <?php endforeach; ?>
                    </div>
                 </section>
                 
                 <section class="rental" id="products">
                <h2>Rental Services</h2>
                <div class="product-container">
                <?php foreach ($rental_products as $product): ?>
                    <?php include '/xampp/htdocs/WaterRefillingSystem/php/rental_product_card.php'; ?>
                <?php endforeach; ?>                                                                
            </div>
          </section>

            <section class="products" id="products">
                <h2>Products & Accessories</h2>
                <div class="product-container">
                <?php foreach ($normal_products as $product): ?>
                    <?php include '/xampp/htdocs/WaterRefillingSystem/php/product_card.php'; ?>
                <?php endforeach; ?>
            </div> 
          </section>

      

            <div></div>
                    <!-- Contact Information Section -->
                    <section class="contact" id="contactus">
                        <div>
                        <h2>Contact Us</h2>
                        <div class="contact-container">
                            <div class="contact-info">
                                <p><strong>📍 Address:</strong> Reclusado St.,  Apokon, Tagum City Davao Del Norte</p>
                                <p><strong>📞 Phone:</strong> +63 912 345 6789</p>
                                <p><strong>✉️ Email:</strong> support@jzwater.com</p>
                                <p><strong>🕒 Business Hours:</strong> Mon-Sat: 8 AM - 8 PM | Sun: 9 AM - 5 PM</p>
                            </div>
                            <div class="contact-map">
                                <iframe src="https://maps.google.com/maps?q=/Reclusado,+tagum%20Philippines&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                                    width="500" height="500" frameborder="0" ></iframe>
                                </div>
                            </div>
                            </div>
                        </section>

                    <!-- Developers Section -->
                    <section class="developers" id="developers">
                        <h2>Organizational Structure</h2>
                        <div class="dev-container">
                            <div class="developer">
                                <img src="/images/(6) REX.jpg" alt="Developer 1">
                                <h3>Rexcel Jay A. Lusica</h3>
                                <p>Lead Developer</p>
                                <p>The Lead Developer of JZ Water Refilling Station’s online system.
                                    With expertise in full-stack development, he specializes in building efficient, user-friendly, and secure web applications. 
                                    Passionate about technology and innovation, Rexcel ensures seamless functionality and a great user experience. 
                                    His dedication to coding and problem-solving drives the continuous improvement of the system.</p>
                            </div>
                            <div class="developer">
                                <img src="/images/joren.jpg" alt="Developer 2">
                                <h3>Joren Montejo</h3>
                                <p>Frontend Designer</p>
                                <p> A skilled Frontend Designer focused on creating visually appealing and user-friendly web interfaces. 
                                    With expertise in HTML, CSS, and JavaScript, 
                                    Joren ensures smooth, responsive, and intuitive user experiences.

                                </p>
                    
                            </div>
                            <div class="developer">
                                <img src="/images/jomarie.jpg" alt="Developer 3">
                                <h3>Jomarie Travilla</h3>
                                <p>Backend Engineer</p>
                                <p> Aa Backend Engineer with expertise in server-side technologies and databases.
                                    Skilled in languages like Java and Python, 
                                    Jomarie ensures efficient, secure, and scalable backend systems.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Footer -->
                    <footer>
                        <p>&copy; 2024 JZ Water Refilling Station | All Rights Reserved.</p>
                    </footer>



                    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/JavaScript/jquery-3.6.0.min.js"></script>
    <script src ="/javascript/script.js"></script>

            <script>
             document.addEventListener("DOMContentLoaded", function () {
              console.log("Checking jQuery:", typeof $ !== "undefined"); // Debugging step
                        });
                        
             document.addEventListener("DOMContentLoaded", function () {
                const form = document.getElementById("signupForm");
                const username = document.getElementById("username");
                const email = document.getElementById("email");
                const contact = document.getElementById("contact");
                const password = document.getElementById("password");
                const confirmPassword = document.getElementById("confirmPassword");
                const submitBtn = document.getElementById("signupBtn");
                const responseMessage = document.getElementById("responseMessage");

                const errors = {
                    username: document.getElementById("usernameError"),
                    email: document.getElementById("emailError"),
                    contact: document.getElementById("contactError"),
                    password: document.getElementById("passwordError"),
                    confirmPassword: document.getElementById("confirmPasswordError"),
                };

                function showNotification(message, type = "error") {
                    const notification = document.getElementById("notification");
                    notification.textContent = message;
                    notification.className = `notification ${type}`;
                    notification.style.display = "block";
                    setTimeout(() => {
                        notification.style.display = "none";
                    }, 5000);
                }

                function hideError(field) {
                    errors[field].textContent = "";
                    errors[field].style.display = "none";
                }

                function showError(field, message) {
                    errors[field].textContent = message;
                    errors[field].style.display = "block";
                }

                function validateGmail(email) {
                    return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
                }

                form.addEventListener("submit", function (event) {
                    event.preventDefault();
                    let valid = true;

                    // Clear previous errors
                    Object.keys(errors).forEach((field) => hideError(field));

                    // Validation Checks
                    if (username.value.trim().length < 3) {
                        showError("username", "Username must be at least 3 characters");
                        valid = false;
                    }

                    if (!validateGmail(email.value)) {
                        showError("email", "Email must be a valid @gmail.com address");
                        valid = false;
                    }

                    if (!/^09\d{9}$/.test(contact.value)) {
                        showError("contact", "Contact number must start with '09' and be exactly 11 digits.");
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
                        submitBtn.disabled = true; // Disable button to prevent multiple submissions

                        // Prepare data for AJAX request
                        const formData = new FormData();
                        formData.append("register_username", username.value.trim());
                        formData.append("register_email", email.value.trim());
                        formData.append("register_contact", contact.value.trim());
                        formData.append("register_password", password.value.trim());

                        fetch("/php/register1.php", {
                        method: "POST",
                        body: new FormData(document.getElementById("signupForm")),
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("HTTP error! Status: " + response.status);
                        }
                        return response.text(); // First, get text response
                    })
                    .then(text => {
                        try {
                            return JSON.parse(text); // Then, try parsing as JSON
                        } catch (error) {
                            console.error("Invalid JSON response:", text);
                            throw new Error("Server returned invalid JSON.");
                        }
                    })
                    .then(data => {
                        if (data.error) {
                            console.error("Server Error:", data.error);
                            document.getElementById("responseMessage").textContent = data.error;
                        } else {
                            document.getElementById("responseMessage").textContent = data.success;
                        }
                    })
                    .catch(error => {
                        console.error("Fetch Error:", error);
                    });

                    }
                });
            });




                            document.addEventListener("DOMContentLoaded", function() {
                            fetch("/xampp/htdocs/WaterRefillingSystem/php/products.php")
                            .then(response => response.json())
                            .then(products => {
                                const refillContainer = document.getElementById("waterRefillContainer");
                                const productContainer = document.getElementById("productContainer");
                                const rentalContainer = document.getElementById("rentalContainer");

                                        products.forEach(product => {
                                    let productHTML = `
                                        <div class="product">
                                            <img src="${product.photo ? product.photo : '/images/1.png'}" alt="${product.name}">
                                            <h3>${product.name}</h3>
                                            <p><strong>Description:</strong> ${product.description}</p>  <!-- Added description -->
                                            <p><strong>Price:</strong> ₱${product.price}</p>
                                            <p><strong>Stock:</strong> ${product.stock} available</p>
                                            <a id="form-open" class="button-order-now">Order Now</a>
                                        </div>
                                    `;

                                    if (product.product_type === "refill") {
                                        refillContainer.innerHTML += productHTML;
                                    } else if (product.product_type === "normal") {
                                        productContainer.innerHTML += productHTML;
                                    } else if (product.product_type === "renting") {
                                        rentalContainer.innerHTML += productHTML.replace("Order Now", "Rent Now");
                                    }
                                });
                            })
                            .catch(error => console.error("Error fetching products:", error));
                        });


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
                                
                            
                                    document.addEventListener("DOMContentLoaded", function () {
                                        const sections = document.querySelectorAll("section");
                                        const navLinks = document.querySelectorAll(".nav_link");
                                    
                                        function highlightNav() {
                                            let current = "";
                                            sections.forEach((section) => {
                                                const sectionTop = section.offsetTop;
                                                const sectionHeight = section.clientHeight;
                                                if (pageYOffset >= sectionTop - sectionHeight / 3) {
                                                    current = section.getAttribute("id");
                                                }
                                            });
                                    
                                            navLinks.forEach((link) => {
                                                link.classList.remove("active");
                                                if (link.getAttribute("href").substring(1) === current) {
                                                    link.classList.add("active");
                                                }
                                            });
                                        }
                                    
                                        window.addEventListener("scroll", highlightNav);
                                    
                                        // Smooth Scroll for Navbar Links
                                        navLinks.forEach((link) => {
                                            link.addEventListener("click", function (e) {
                                                e.preventDefault();
                                                const targetId = this.getAttribute("href").substring(1);
                                                const targetSection = document.getElementById(targetId);
                                    
                                                window.scrollTo({
                                                    top: targetSection.offsetTop - 50, 
                                                    behavior: "smooth",
                                                });
                                            });
                                        });
                                    });
                                    

                                    function showToast(message) {
                            const toastMessage = document.getElementById("toastMessage");
                            const formToast = document.getElementById("formToast");

                            toastMessage.textContent = message;

                            // Adjust width based on message length
                            let length = message.length;
                            let newWidth = Math.min(500, Math.max(200, length * 8)); // Auto-adjust width

                            formToast.style.width = newWidth + "px";

                            // Show toast using Bootstrap's Toast component
                            let toast = new bootstrap.Toast(formToast);
                            toast.show();
                        }

                        $(document).ready(function () {
                            $.ajax({
                                url: "/xampp/htdocs/WaterRefillingSystem/php/products.php",
                                type: "GET",
                                dataType: "json",
                                success: function (response) {
                                    if (response.status === "success") {
                                        displayProducts(response.data);
                                    }
                                },
                                error: function () {
                                    console.log("Failed to fetch products.");
                                }
                            });

                            function displayProducts(products) {
                                let refillContainer = $("#waterRefillContainer");
                                let productContainer = $("#productContainer");
                                let rentalContainer = $("#rentalContainer");

                                refillContainer.empty();
                                productContainer.empty();
                                rentalContainer.empty();

                                // Water Refill Products
                                products.refill.forEach(product => {
                                    refillContainer.append(`
                                        <div class="product-card">
                                            <img src="${product.photo}" alt="${product.name}">
                                            <h3>${product.name}</h3>
                                            <p>${product.description}</p>
                                            <p><strong>₱${product.price}</strong></p>
                                            <button>Add to Cart</button>
                                        </div>
                                    `);
                                });

                                // Normal Products & Accessories
                                products.normal.forEach(product => {
                                    productContainer.append(`
                                        <div class="product-card">
                                            <img src="${product.photo}" alt="${product.name}">
                                            <h3>${product.name}</h3>
                                            <p>${product.description}</p>
                                            <p><strong>₱${product.price}</strong></p>
                                            <button>Add to Cart</button>
                                        </div>
                                    `);
                                });

                                // Rental Services
                                products.renting.forEach(product => {
                                    rentalContainer.append(`
                                        <div class="product-card">
                                            <img src="${product.photo}" alt="${product.name}">
                                            <h3>${product.name}</h3>
                                            <p>${product.description}</p>
                                            <p><strong>₱${product.price}</strong></p>
                                            <button>Rent Now</button>
                                        </div>
                                    `);
                                });
                            }
                        });
                        </script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let status = "<?php echo trim($product['status']); ?>";
        let stock = <?php echo $product['stock']; ?>;
        
        let quantityInput = document.getElementById("quantity");
        let addToCartButton = document.getElementById("add_to_cart");

        if (status !== "Available" || stock < 1) {
            quantityInput.disabled = true;
            addToCartButton.disabled = true;

            // Apply CSS class for better visual indication
            addToCartButton.classList.add("disabled");
        }
    });
</script>
           
    </body>
    </html>