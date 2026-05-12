// ── Toggle Sign In / Sign Up (animation originale) ──────────────────────────
const container  = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn    = document.getElementById('login');

registerBtn.addEventListener('click', () => container.classList.add('active'));
loginBtn.addEventListener('click',    () => container.classList.remove('active'));


// ── Récupération des formulaires ─────────────────────────────────────────────
const signUpForm  = document.querySelector('.form-container.sign-up form');
const signInForm  = document.querySelector('.form-container.sign-in form');

// Inputs Sign Up
const signUpName     = signUpForm.querySelector('input[type="text"]');
const signUpEmail    = signUpForm.querySelector('input[type="email"]');
const signUpPassword = signUpForm.querySelector('input[type="password"]');
const signUpBtn      = signUpForm.querySelector('button');

// Inputs Sign In
const signInEmail    = signInForm.querySelector('input[type="email"]');
const signInPassword = signInForm.querySelector('input[type="password"]');
const signInBtn      = signInForm.querySelector('button');


// ── Fonction utilitaire : afficher un message sous le formulaire ─────────────
function showMessage(form, message, isSuccess) {
    let msg = form.querySelector('.auth-msg');
    if (!msg) {
        msg = document.createElement('p');
        msg.className = 'auth-msg';
        msg.style.cssText = 'margin-top:10px;font-size:13px;font-weight:500;text-align:center';
        form.appendChild(msg);
    }
    msg.textContent  = message;
    msg.style.color  = isSuccess ? '#28a745' : '#dc3545';
}


// ── Appel AJAX vers auth_handler.php ────────────────────────────────────────
async function callAuth(payload, form, btn) {
    btn.disabled    = true;
    btn.textContent = '...';

    try {
        const fd = new FormData();
        for (const [k, v] of Object.entries(payload)) fd.append(k, v);

        const res  = await fetch('auth_handler.php', { method: 'POST', body: fd });
        const data = await res.json();

        showMessage(form, data.message, data.success);

        if (data.success) {
            setTimeout(() => {
                window.location.href = 'listBooks.php';
            }, 1200);
        }
    } catch (err) {
        showMessage(form, 'Erreur réseau. Veuillez réessayer.', false);
    } finally {
        btn.disabled    = false;
        btn.textContent = btn === signUpBtn ? 'Sign Up' : 'Sign In';
    }
}


// ── Sign Up ──────────────────────────────────────────────────────────────────
signUpBtn.addEventListener('click', (e) => {
    e.preventDefault();

    const nom      = signUpName.value.trim();
    const email    = signUpEmail.value.trim();
    const password = signUpPassword.value;

    if (!nom || !email || !password) {
        showMessage(signUpForm, 'Please fill in all fields.', false);
        return;
    }

    callAuth({ nom, email, password }, signUpForm, signUpBtn);
});


// ── Sign In ──────────────────────────────────────────────────────────────────
signInBtn.addEventListener('click', (e) => {
    e.preventDefault();

    const email    = signInEmail.value.trim();
    const password = signInPassword.value;

    if (!email || !password) {
        showMessage(signInForm, 'Please fill in all fields.', false);
        return;
    }

    callAuth({ nom: '', email, password }, signInForm, signInBtn);
});
