document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="password"]').forEach((input) => {
        let group = input.closest('.input-group');

        if (!group) {
            group = document.createElement('div');
            group.className = 'input-group';

            [...input.classList].filter(c => /^mb-\d+$/.test(c)).forEach(c => {
                group.classList.add(c);
                input.classList.remove(c);
            });

            input.parentNode.insertBefore(group, input);
            group.appendChild(input);
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary';
        button.innerHTML = '<i class="bi bi-eye"></i>';
        button.setAttribute('aria-label', 'Намоиш додани парол');
        button.setAttribute('aria-pressed', 'false');

        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.innerHTML = show
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
            button.setAttribute('aria-label', show ? 'Пинҳон кардани парол' : 'Намоиш додани парол');
            button.setAttribute('aria-pressed', String(show));
        });

        group.appendChild(button);
    });
});
