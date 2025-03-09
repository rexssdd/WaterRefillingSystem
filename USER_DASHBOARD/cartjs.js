
// Show Place Order modal
document.getElementById('placeOrderBtn').addEventListener('click', () => {
    document.getElementById('placeOrderModal').style.display = 'block';
  });
  
  // Close Place Order modal
  document.getElementById('close').addEventListener('click', () => {
    document.getElementById('placeOrderModal').style.display = 'none';
  });
  
  // Show Manage Address modal
  document.getElementById('updateAddressBtn').addEventListener('click', () => {
    document.getElementById('manageAddressModal').style.display = 'block';
  });
  
  // Close Manage Address modal
  document.getElementById('closeManageAddressModal').addEventListener('click', () => {
    document.getElementById('manageAddressModal').style.display = 'none';
  });
  
  // Select address and update dropdown
  document.querySelectorAll('.address-item').forEach(item => {
    item.addEventListener('click', () => {
      const selectedAddress = item.textContent.trim();
      const addressId = item.getAttribute('data-id');
  
      const addressDropdown = document.getElementById('address');
      addressDropdown.innerHTML = `<option value="${addressId}">${selectedAddress}</option>`;
      document.getElementById('manageAddressModal').style.display = 'none';
    });
  });
  
  document.addEventListener('DOMContentLoaded', () => {
    const addressList = document.getElementById('addressList');
  
    if (addressList) { // Check if the element exists
      // EDIT BUTTONS (Event delegation)
      addressList.addEventListener('click', (e) => {
        if (e.target.classList.contains('edit-address-btn')) {
          const button = e.target;
          const addressId = button.getAttribute('data-id');
          const barangay = button.getAttribute('data-barangay');
          const street = button.getAttribute('data-street');
          const landmark = button.getAttribute('data-landmark');
  
          // Populate edit form
          document.getElementById('editAddressId').value = addressId;
          document.getElementById('editBarangay').value = barangay;
          document.getElementById('editStreet').value = street;
          document.getElementById('editLandmark').value = landmark;
  
          // Show edit modal
          const editModal = document.getElementById('editAddressModal');
          if (editModal) {
            editModal.style.display = 'block';
          }
        }
      });
  
      // DELETE BUTTONS (Event delegation)
      addressList.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-address-btn')) {
          const button = e.target;
          const addressId = button.getAttribute('data-id');
          if (confirm('Are you sure you want to remove this address?')) {
            fetch('delete_address.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `address_id=${addressId}`
            })
            .then(response => response.text())
            .then(data => {
              if (data.trim() === 'success') {
                alert('Address removed successfully!');
                button.closest('li').remove();
              } else {
                alert('Failed to remove address.');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred.');
            });
          }
        }
      });
    } else {
      console.error('Error: #addressList not found.');
    }
  });
  
  
  // Handle order confirmation
  document.getElementById('orderForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const address = document.getElementById('address').value;
    const paymentMethod = document.getElementById('paymentMethod').value;
  
    if (!address || !paymentMethod) {
      alert('Please select an address and payment method.');
      return;
    }
  
    alert(`Order confirmed!\nAddress: ${address}\nPayment Method: ${paymentMethod}`);
  });
  
  // Select an address from the Manage Address modal
  document.querySelectorAll('.address-item').forEach(item => {
    item.addEventListener('click', () => {
      const selectedAddress = item.textContent.trim();
      const addressId = item.getAttribute('data-id');
  
      const addressDropdown = document.getElementById('address');
      addressDropdown.innerHTML = `<option value="${addressId}">${selectedAddress}</option>`;
      document.getElementById('manageAddressModal').style.display = 'none';
    });
  });
  
  
      document.getElementById('addressList').addEventListener('click', (e) => {
          if (e.target.classList.contains('select-address')) {
              const selectedAddress = e.target.getAttribute('data-address');
              const selectedAddressId = e.target.getAttribute('data-address-id');
              const addressDropdown = document.getElementById('address');
              addressDropdown.innerHTML = `<option value="${selectedAddressId}" selected>${selectedAddress}</option>`;
              const manageAddressModal = bootstrap.Modal.getInstance(document.getElementById('manageAddressModal'));
              manageAddressModal.hide();
          }
      });
  
      document.getElementById('addAddressForm').addEventListener('submit', (e) => {
          e.preventDefault();
          const barangay = document.getElementById('barangay').value;
          const street = document.getElementById('street').value;
          const landmark = document.getElementById('landmark').value;
          const newAddress = `${barangay}, ${street} (Landmark: ${landmark})`;
          const newAddressId = Date.now();
  
          const addressList = document.getElementById('addressList');
          addressList.innerHTML += `<li class="list-group-item">
              ${newAddress}
              <button class="btn btn-sm btn-secondary select-address" data-address-id="${newAddressId}" data-address="${newAddress}">Select</button>
          </li>`;
  
          document.getElementById('addAddressForm').reset();
          const addAddressModal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
          addAddressModal.hide();
      });
  //dss
  
  document.querySelectorAll('input[type=number]').forEach(input => {
      input.addEventListener('change', function() {
          if (this.value < 1) {
              alert('Invalid quantity! Must be at least 1.');
              this.value = 1;
          }
      });
  });
  
  document.addEventListener("DOMContentLoaded", function () {
      const orderForm = document.getElementById("orderForm");
  
      // Ensure quantity inputs are valid (min 1)
      document.querySelectorAll('input[type=number]').forEach(input => {
          input.addEventListener('change', function() {
              if (this.value < 1) {
                  alert('Invalid quantity! Must be at least 1.');
                  this.value = 1;
              }
          });
      });
  
      // Handle form submission
      if (orderForm) {
          orderForm.addEventListener("submit", async function (e) {
              e.preventDefault();
  
              const orderTotalElement = document.getElementById("orderTotal");
              if (!orderTotalElement) {
                  alert("Order total is missing.");
                  return;
              }
  
              const totalPrice = parseFloat(orderTotalElement.textContent.trim().replace('₱', ''));
              if (isNaN(totalPrice) || totalPrice <= 0) {
                  alert("Invalid total price. Please check your order.");
                  return;
              }
  
              document.getElementById("totalPrice").value = totalPrice;
  
              const formData = new FormData(orderForm);
  
              try {
                  const response = await fetch("/php/place_order.php", {
                      method: "POST",
                      body: formData,
                  });
  
                  const data = await response.json(); // Get the response as JSON
  
                  if (!response.ok || !data.success) {
                      throw new Error(data.message || "Failed to place order.");
                  }
  
                  alert("Order placed successfully!");
  
                  // Clear the cart and refresh the page
                  location.reload(); // This will refresh the page after a successful order
  
              } catch (error) {
                  console.error("Error:", error.message);
                  alert(error.message || "An error occurred while placing your order.");
              }
          });
      }
  
  });
  
  
  
  
    const updateAddressButton = document.querySelector('[data-bs-target="#manageAddressModal"]');
  if (updateAddressButton) {
      updateAddressButton.addEventListener("click", function () {
          let modal = new bootstrap.Modal(document.getElementById("manageAddressModal"));
          modal.show();
      });
  } else {
      console.error("Error: Update Address button not found.");
  }
  
  document.addEventListener("DOMContentLoaded", () => {
      const userId = 1;  // Replace with actual logged-in user ID
      fetchAddresses(userId);
  });
  
  function fetchAddresses(userId) {
      fetch(`/php/fetch_user_address.php?user_id=${userId}`)
          .then(response => response.text()) // <-- Get raw response first
          .then(text => {
              console.log("Raw response:", text); // Log raw output
              return JSON.parse(text); // Try to parse JSON
          })
          .then(data => {
              console.log("Parsed JSON:", data); // Ensure JSON is correct
  
              const addressList = document.getElementById("addressList");
              addressList.innerHTML = "";
  
              if (!data.success) {
                  addressList.innerHTML = `<li class='list-group-item'>${data.message || "No addresses found"}</li>`;
                  return;
              }
  
              const addresses = data.data || [];
              if (addresses.length === 0) {
                  addressList.innerHTML = "<li class='list-group-item'>No addresses found</li>";
                  return;
              }
  
              addresses.forEach(address => {
                  const li = document.createElement("li");
                  li.classList.add("list-group-item");
                  li.textContent = `${address.street}, ${address.barangay}, Landmark: ${address.landmark}, Note: ${address.note}`;
                  addressList.appendChild(li);
              });
          })
          .catch(error => console.error("Error fetching addresses:", error));
  }
  
  document.addEventListener("DOMContentLoaded", function () {
      const closeButton = document.querySelector("#placeOrderModal .btn-close");
  
      if (closeButton) {
          closeButton.addEventListener("click", function () {
              const placeOrderModal = new bootstrap.Modal(document.getElementById("placeOrderModal"));
              placeOrderModal.hide();
          });
      }
  });
  
  document.addEventListener("DOMContentLoaded", function () {
    const addAddressModal = document.getElementById("addAddressModal");
    const placeOrderModal = new bootstrap.Modal(document.getElementById("home"));
  
    addAddressModal.addEventListener("hidden.bs.modal", function () {
      placeOrderModal.show(); // Reopen Place Order Modal when Add Address Modal is closed
    });
  });
  
  document.getElementById('orderForm').addEventListener('submit', async (e) => {
    e.preventDefault();
  
    // Simulate form submission (Replace this with your actual order submission logic)
    console.log('Order submitted!');
  
    // Close the modal manually
    const modal = bootstrap.Modal.getInstance(document.getElementById('placeOrderModal'));
    modal.hide();
  });
    
  async function loadAddressNote() {
    try {
      const response = await fetch('/php/fetch_address.php');
      const data = await response.json();
  
      if (data.note) {
        document.getElementById('orderNote').value = data.note;
      }
    } catch (error) {
      console.error('Error fetching address note:', error);
    }
  }
  
  // Call function when the page loads
  document.addEventListener('DOMContentLoaded', loadAddressNote);
  
  document.querySelector('form').addEventListener('submit', function (e) {
      e.preventDefault();
  
      const formData = new FormData(this);
  
      fetch('/php/save_address.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              alert(data.message);
              closeModal(); // Hide the modal
              location.reload(); // Refresh to show the new address
          } else {
              alert('Error: ' + data.message);
          }
      })
      .catch(error => {
          console.error('Error adding address:', error);
      });
  });
  
      // Fetch and display user's address
  document.addEventListener('DOMContentLoaded', async () => {
    await fetchAddress();
  });
  
  async function fetchAddress() {
      try {
          const response = await fetch("/php/fetch_address.php"); // Adjust the endpoint if needed
          const data = await response.json();
  
          const addressList = document.getElementById("addressList"); // Ensure this ID exists
          if (!addressList) {
              console.error("Error: addressList element not found.");
              return; // Stop execution if the element is missing
          }
  
          if (data.success) {
              addressList.innerHTML = ""; // Clear existing content
  
              data.addresses.forEach(address => {
                  let listItem = document.createElement("li");
                  listItem.classList.add("list-group-item");
                  listItem.textContent = address;
                  addressList.appendChild(listItem);
              });
          } else {
              addressList.innerHTML = "<li class='list-group-item text-danger'>No addresses found.</li>";
          }
      } catch (error) {
          console.error("Error fetching address:", error);
      }
  }
  
  // Call fetchAddress only when DOM is fully loaded
  document.addEventListener("DOMContentLoaded", fetchAddress);
  
  
  // Add new address
  document.getElementById('addAddressForm').addEventListener('submit', async (e) => {
    e.preventDefault();
  
    const barangay = document.getElementById('barangay').value;
    const street = document.getElementById('street').value;
    const landmark = document.getElementById('landmark').value;
    const note = document.getElementById('note').value;
  
    try {
      const response = await fetch('/php/add_address.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ barangay, street, landmark, note })
      });
  
      const result = await response.json();
      if (result.status === 'success') {
        fetchAddress();
        const addAddressModal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
        addAddressModal.hide();
  
        // Ensure modal backdrop is removed
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
  
        // Optionally, show the "Place Order" modal
        const placeOrderModal = new bootstrap.Modal(document.getElementById('placeOrderModal'));
        placeOrderModal.show();
      } else {
        alert('Failed to add address. Please try again.');
      }
    } catch (error) {
      console.error('Error adding address:', error);
    }
  });
  
  
  
  
  // Manage address (show the update modal)
  document.addEventListener("DOMContentLoaded", function() {
      document.querySelector('[data-bs-target="#manageAddressModal"]').addEventListener("click", function() {
          const modal = new bootstrap.Modal(document.getElementById("manageAddressModal"));
          modal.show();
      });
  });
  
  
  // Fetch address for editing
  async function fetchAddressForEdit() {
    try {
      const response = await fetch('fetch_address.php');
      const data = await response.json();
  
      document.getElementById('editBarangay').value = data.barangay || '';
      document.getElementById('editStreet').value = data.street || '';
      document.getElementById('editLandmark').value = data.landmark || '';
    } catch (error) {
      console.error('Error fetching address for edit:', error);
    }
  }
  
       document.getElementById('addAddressForm').addEventListener('submit', function (e) {
      e.preventDefault();
  
      // Get form values
      const barangay = document.getElementById('barangay').value;
      const street = document.getElementById('street').value;
      const landmark = document.getElementById('landmark').value;
  
      // Validate fields
      if (!barangay || !street || !landmark) {
        alert('Please fill in all address fields.');
        return;
      }
  
      // Update the main modal with new address
      const addressField = document.getElementById('address');
      addressField.textContent = `${street}, ${barangay}, Landmark: ${landmark}`;
      addressField.removeAttribute('readonly');
  
      // Close modal
      const addAddressModal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
      addAddressModal.hide();
    });
  document.addEventListener("DOMContentLoaded", function () {
      document.getElementById("placeOrderBtn").addEventListener("click", function () {
          fetchCartItems();
      });
  });
  function fetchCartItems() {
      fetch("/php/fetch_cart.php")
          .then(response => response.text()) // Get raw response
          .then(data => {
              console.log("Raw response:", data); // Debugging output
  
              try {
                  let jsonData = JSON.parse(data); // Convert to JSON
                  if (jsonData.status === "success") {
                      console.log("Cart Items:", jsonData.items);
  
                      let orderSummary = document.getElementById("orderSummary");
                      let orderTotal = document.getElementById("orderTotal");
  
                      orderSummary.innerHTML = "";
                      jsonData.items.forEach(item => {
                          let listItem = document.createElement("li");
                          listItem.className = "list-group-item bg-dark text-white";
                          listItem.innerHTML = `${item.product_name} - ${item.quantity} x ₱${item.price.toFixed(2)} = ₱${item.subtotal.toFixed(2)}`;
                          orderSummary.appendChild(listItem);
                      });
  
                      orderTotal.textContent = jsonData.total.toFixed(2);
                  } else {
                      alert("Error: " + jsonData.message);
                  }
              } catch (error) {
                  console.error("JSON Parsing Error:", error, "\nResponse:", data);
                  alert("Invalid server response. Check console for details.");
              }
          })
          .catch(error => console.error("Fetch Error:", error));
  }
  
  
  function fetchCartItems() {
    fetch('/php/fetch_cart.php')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          const items = data.items;
          const orderSummary = document.getElementById('orderSummary');
          const orderTotal = document.getElementById('orderTotal');
          const totalPriceInput = document.getElementById('totalPrice');
  
          if (!orderSummary || !orderTotal || !totalPriceInput) {
            console.error('Error: Required elements not found in the DOM.');
            return;
          }
  
          // Clear previous content
          orderSummary.innerHTML = '';
  
          // Add each item to the modal
          let total = 0;
          items.forEach(item => {
            const itemDetails = `
              <li class="list-group-item">
                <strong>${item.product_name}</strong> - 
                Quantity: ${item.quantity}, 
                Price: ₱${parseFloat(item.price).toFixed(2)}, 
                Subtotal: ₱${parseFloat(item.subtotal).toFixed(2)}
              </li>
            `;
            total += parseFloat(item.subtotal);
            orderSummary.innerHTML += itemDetails;
          });
  
          // Update total price
          orderTotal.textContent = total.toFixed(2);
          totalPriceInput.value = total.toFixed(2);
  
          // Show the modal
          const placeOrderModal = new bootstrap.Modal(document.getElementById('placeOrderModal'));
          placeOrderModal.show();
        } else {
          console.error('Failed to fetch cart items:', data.message);
        }
      })
      .catch(error => console.error('Error fetching cart items:', error));
  }
  
  // Add event listener to place order button
  document.getElementById('placeOrderBtn').addEventListener('click', fetchCartItems);
  
  
      document.addEventListener("DOMContentLoaded", function () {
      const placeOrderModal = new bootstrap.Modal(document.getElementById("placeOrderModal"));
      const orderSummary = document.getElementById("orderSummary");
      const orderTotal = document.getElementById("orderTotal");
      const addressField = document.getElementById("address");
  
      document.getElementById("placeOrderBtn").addEventListener("click", function () {
          fetch("/php/fetch_order_details.php") // Update path accordingly
              .then(response => response.json())
              .then(data => {
                  if (data.status === "error") {
                      alert(data.message);
                      return;
                  }
  
                  // Fill address field
                  addressField.value = `${data.address.street}, ${data.address.barangay}, Landmark: ${data.address.landmark}\nNote: ${data.address.note}`;
  
                  // Display order summary
                  orderSummary.innerHTML = "";
                  data.items.forEach(item => {
                      orderSummary.innerHTML += `
                          <li class="list-group-item d-flex justify-content-between">
                              ${item.product_name} (x${item.quantity})
                              <span>₱${item.subtotal.toFixed(2)}</span>
                          </li>`;
                  });
  
                  // Display total price
                  orderTotal.textContent = data.total.toFixed(2);
  
                  // Show modal
                  placeOrderModal.show();
              })
              .catch(error => console.error("Error:", error));
      });
  });
  
  document.getElementById('placeOrderBtn').addEventListener('click', function() {
      const orderSummary = document.getElementById('orderSummary');
      const orderTotal = document.getElementById('orderTotal');
      let total = 0;
      orderSummary.innerHTML = '';
  
      document.querySelectorAll('.cart .list-group-item').forEach(item => {
          const name = item.querySelector('h5').textContent;
          const price = parseFloat(item.querySelector('p').textContent.replace('Price: ₱', ''));
          const quantity = item.querySelector('input[type="number"]').value;
          const itemTotal = price * quantity;
          total += itemTotal;
  
          const listItem = document.createElement('li');
          listItem.className = 'list-group-item bg-dark text-white';
          listItem.textContent = `${name} x${quantity} - ₱${itemTotal.toFixed(2)}`;
          orderSummary.appendChild(listItem);
      });
  
      orderTotal.textContent = total.toFixed(2);
  
      // Show the modal
      const placeOrderModal = new bootstrap.Modal(document.getElementById('placeOrderModal'));
      placeOrderModal.show();
  });
  
  
    document.getElementById('orderForm').addEventListener('submit', function(e) {
      e.preventDefault();
      new bootstrap.Modal(document.getElementById('placeOrderModal')).hide();
      new bootstrap.Modal(document.getElementById('successModal')).show();
    });
  
      
      // Handle quantity change via AJAX
      document.querySelectorAll('.quantity-input').forEach(input => {
          input.addEventListener('change', function () {
              let cartId = this.dataset.cartId;
              let productId = this.dataset.productId;
              let newQuantity = parseInt(this.value);
              let maxStock = parseInt(this.dataset.stock);
  
              if (newQuantity <= 0) {
                  alert("Quantity must be at least 1.");
                  this.value = 1;
                  return;
              }
  
              if (newQuantity > maxStock) {
                  alert("Not enough stock available!");
                  this.value = maxStock;
                  return;
              }
  
              fetch('/php/update_cart.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ cart_id: cartId, product_id: productId, quantity: newQuantity })
              })
              .then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      document.getElementById('total_' + cartId).textContent = "₱" + data.new_total;
                  } else {
                      alert(data.message);
                  }
              })
              .catch(error => console.error('Error:', error));
          });
      });
  
  
  document.getElementById('placeOrderBtn').addEventListener('click', function () {
      fetch('/php/get_cart_items.php') // Fetch cart items from database
          .then(response => response.json())
          .then(data => {
              const orderSummary = document.getElementById('orderSummary');
              const orderTotal = document.getElementById('orderTotal');
              let total = 0;
              orderSummary.innerHTML = '';
  
              data.items.forEach(item => {
                  const itemTotal = item.price * item.quantity;
                  total += itemTotal;
  
                  const listItem = document.createElement('li');
                  listItem.className = 'list-group-item bg-dark text-white';
                  listItem.textContent = `${item.name} x${item.quantity} - ₱${itemTotal.toFixed(2)}`;
                  orderSummary.appendChild(listItem);
              });
  
              orderTotal.textContent = total.toFixed(2);
          })
          .catch(error => console.error('Error fetching cart items:', error));
  });
  
  document.getElementById('orderForm').addEventListener('submit', function (e) {
      e.preventDefault();
  
      const address = document.getElementById('address').value.trim();
      const paymentMethod = document.getElementById('paymentMethod').value;
  
      if (address === '') {
          alert('Please enter a delivery address.');
          return;
      }
  
      fetch('/php/fetch_cart.php') // Get cart items before placing order
          .then(response => response.json())
          .then(data => {
  
  
              const orderData = {
                  items: data.items,
                  payment_method: paymentMethod
              };
  
              return fetch('/php/place_order.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify(orderData)
              });
          })
          .then(response => response.json())
          .then(result => {
              if (result.status === 'success') {
                  new bootstrap.Modal(document.getElementById('placeOrderModal')).hide();
                  new bootstrap.Modal(document.getElementById('successModal')).show();
              } else {
                  alert('Order failed: ' + result.message);
              }
          })
          .catch(error => console.error('Error placing order:', error));
  });
  
  function fetchCartCount() {
      fetch('/php/fetch_cart_count.php')
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