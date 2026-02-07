const getTauriInvoke = () => {
    try {
        return window.__TAURI__.core.invoke;
    } catch (e) {
        console.error('Tauri API not found!', e);
        return null;
    }
};

const form = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const btnText = loginBtn.querySelector('.btn-text');
const btnLoader = loginBtn.querySelector('.btn-loader');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');

// API URL - Auto-switch based on environment (Dev = Localhost, Build = Production)
const BASE_URL = import.meta.env.DEV ? 'http://localhost:8000' : 'https://test.asadvanceit.com';
const API_URL = `${BASE_URL}/api/agent/login`;

// Heartbeat system
let hardwareId = null;
let computerName = null;

async function startHeartbeat() {
    const invoke = getTauriInvoke();
    if (!invoke) return;

    try {
        // Fetch HWID and PC Name from Rust
        hardwareId = await invoke('get_hwid');
        computerName = await invoke('get_computer_name');

        // Initial and Interval heartbeat
        const sendHeartbeat = async () => {
            try {
                await fetch(`${BASE_URL}/api/agent/heartbeat`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        hardware_id: hardwareId,
                        computer_name: computerName
                    })
                });
            } catch (e) {
                console.error('Heartbeat failed', e);
            }
        };

        sendHeartbeat();
        setInterval(sendHeartbeat, 30000); // 30 seconds
    } catch (e) {
        console.error('Failed to get device info', e);
    }
}

startHeartbeat();

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    console.log('Login form submitted');

    // Hide messages
    errorMessage.style.display = 'none';
    successMessage.style.display = 'none';

    const invoke = getTauriInvoke();
    if (!invoke) {
        errorMessage.textContent = 'Critical Error: Tauri API not loaded. Is this running inside Tauri?';
        errorMessage.style.display = 'block';
        return;
    }

    // Show loading
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    loginBtn.disabled = true;

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    try {
        console.log('Calling Rust login with:', { email, api_url: API_URL });
        // Call Rust backend to make API request
        const result = await invoke('login', { email, password, apiUrl: API_URL });
        console.log('Rust login result:', result);

        if (result.success) {
            successMessage.textContent = 'Login successful! Opening browser...';
            successMessage.style.display = 'block';

            // Open magic URL in browser - using bracket notation to be safe with casing
            let magicUrl = result.magic_url || result.magicUrl;

            // Force HTTP in Dev mode to avoid SSL errors with php artisan serve
            if (import.meta.env.DEV && magicUrl.startsWith('https://')) {
                console.log('Dev mode: Converting magic link to HTTP');
                magicUrl = magicUrl.replace('https://', 'http://');
            }

            await invoke('open_browser', { url: magicUrl });
        } else {
            throw new Error(result.message || 'Login failed');
        }
    } catch (error) {
        console.error('Login error:', error);
        // Show the actual error message from Rust instead of generic text
        errorMessage.textContent = error.message || error;
        errorMessage.style.display = 'block';
    } finally {
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        loginBtn.disabled = false;
    }
});
