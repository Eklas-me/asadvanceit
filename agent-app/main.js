const { invoke } = window.__TAURI__.core;

const form = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const btnText = loginBtn.querySelector('.btn-text');
const btnLoader = loginBtn.querySelector('.btn-loader');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');

// API URL - Change this to your production URL when deploying
const API_URL = 'http://127.0.0.1:8000/api/agent/login';

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Hide messages
    errorMessage.style.display = 'none';
    successMessage.style.display = 'none';

    // Show loading
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    loginBtn.disabled = true;

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    try {
        // Call Rust backend to make API request
        const result = await invoke('login', { email, password, apiUrl: API_URL });

        if (result.success) {
            successMessage.textContent = 'Login successful! Opening browser...';
            successMessage.style.display = 'block';

            // Open magic URL in browser
            await invoke('open_browser', { url: result.magic_url });
        } else {
            throw new Error(result.message || 'Login failed');
        }
    } catch (error) {
        errorMessage.textContent = error.message || 'Connection failed. Is the server running?';
        errorMessage.style.display = 'block';
    } finally {
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        loginBtn.disabled = false;
    }
});
