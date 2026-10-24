export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = new Date(`${value}T00:00:00`);

    return new Intl.DateTimeFormat(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
}

export function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
}

export function formatTime(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    if (value.includes('T')) {
        return new Intl.DateTimeFormat(undefined, {
            hour: 'numeric',
            minute: '2-digit',
        }).format(new Date(value));
    }

    const [hours, minutes] = value.split(':').map(Number);
    const date = new Date();
    date.setHours(hours ?? 0, minutes ?? 0, 0, 0);

    return new Intl.DateTimeFormat(undefined, {
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
}

export function todayIso(): string {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    const local = new Date(now.getTime() - offset * 60 * 1000);

    return local.toISOString().slice(0, 10);
}
