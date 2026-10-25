(() => {
    const $ = (selector) => document.querySelector(selector);
    const notice = $('#notice');
    const body = $('#reservas');

    if (!body) return;

    const say = (message, error = false) => {
        notice.textContent = message;
        notice.style.color = error ? '#b00020' : '#075b23';
    };

    const action = (id, estado) => {
        const button = document.createElement('button');
        button.textContent = estado === 'confirmada' ? 'Confirmar' : 'Cancelar';
        button.dataset.id = id;
        button.dataset.estado = estado;

        if (estado === 'cancelada') button.className = 'danger';

        return button;
    };

    const load = async () => {
        const params = new URLSearchParams(new FormData($('#filters')));

        try {
            const response = await fetch(`/api/reservas?${params}`, {cache: 'no-store'});
            const payload = await response.json();

            if (!response.ok) throw Error(payload.error);

            body.textContent = '';
            payload.data.forEach((reserva) => {
                const row = document.createElement('tr');

                const guest = document.createElement('td');
                const detail = document.createElement('a');
                detail.href = `/reservas/${reserva.id}`;
                detail.textContent = reserva.nombre_huesped;
                guest.append(detail);
                row.append(guest);

                const accommodation = document.createElement('td');
                accommodation.textContent = reserva.nombre_alojamiento;
                row.append(accommodation);

                const stay = document.createElement('td');
                stay.textContent = `${reserva.fecha_entrada} - ${reserva.fecha_salida}`;
                row.append(stay);

                const state = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = `estado estado-${reserva.estado}`;
                badge.textContent = reserva.estado;
                state.append(badge);
                row.append(state);

                const amount = document.createElement('td');
                amount.textContent = reserva.importe;
                row.append(amount);

                const actions = document.createElement('td');
                if (reserva.estado === 'pendiente') actions.append(action(reserva.id, 'confirmada'));
                if (reserva.estado !== 'cancelada') actions.append(action(reserva.id, 'cancelada'));
                row.append(actions);
                body.append(row);
            });
        } catch (error) {
            say(error.message, true);
        }
    };

    const change = async (id, estado) => {
        const question = estado === 'confirmada' ? 'Confirmar esta reserva?' : 'Cancelar esta reserva?';
        if (!confirm(question)) return;

        const response = await fetch(`/api/reservas/${id}/estado`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({estado}),
        });
        const payload = await response.json();

        if (!response.ok) return say(payload.error, true);

        await load();
        say(`Reserva ${estado}.`);
    };

    body.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-estado]');
        if (button) change(button.dataset.id, button.dataset.estado);
    });

    $('#filters').addEventListener('submit', (event) => {
        event.preventDefault();
        load();
    });

    $('#create').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const response = await fetch('/api/reservas', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(Object.fromEntries(new FormData(form))),
        });
        const payload = await response.json();

        if (!response.ok) return say(payload.error, true);

        form.reset();
        await load();
        say('Reserva creada.');
    });
})();
