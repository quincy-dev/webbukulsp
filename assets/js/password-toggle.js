document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.dataset.toggleReady === '1') return;
        input.dataset.toggleReady = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'input-group password-input-group';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary password-toggle';
        button.setAttribute('aria-label', 'Tampilkan password');
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
        button.addEventListener('click', function () {
            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            button.innerHTML = showing
                ? '<i class="bi bi-eye-slash" aria-hidden="true"></i>'
                : '<i class="bi bi-eye" aria-hidden="true"></i>';
            button.setAttribute('aria-label', showing ? 'Sembunyikan password' : 'Tampilkan password');
            button.setAttribute('aria-pressed', showing ? 'true' : 'false');
            input.focus();
        });
        wrapper.appendChild(button);
    });
});
