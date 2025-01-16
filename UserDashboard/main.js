const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');
const body = document.body;

function toggleSidebar() {
    sidebar.classList.toggle('active');
    body.classList.toggle('sidebar-open');
}

function showContent(section) {
    if (section === 'dashboard') {
        content.innerHTML = `
            <h2>Welcome to your Dashboard</h2>
            <div class="buttons-container">
                <button class="button">Home</button>
                <button class="button">Orders</button>
                <button class="button">Delivery</button>
                <button class="button">History</button>
            </div>
            <div class="image-container">
                <img src="https://via.placeholder.com/150?text=Water+Bottle" alt="Water Bottle">
            </div>
        `;
    } else if (section === 'profile') {
        content.innerHTML = `
            <h2>Profile Settings</h2>
            <p>Manage your profile information here.</p>
        `;
    }
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}