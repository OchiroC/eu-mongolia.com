// Огноо/цагийн нэгдсэн форматчлал.

function pad(n) {
    return String(n).padStart(2, '0');
}

/** "2025.05.01" хэлбэр */
export function formatDate(value) {
    if (!value) return '';
    const d = new Date(value);
    if (isNaN(d)) return '';
    return `${d.getFullYear()}.${pad(d.getMonth() + 1)}.${pad(d.getDate())}`;
}

/** "2025.05.01 18:00" хэлбэр */
export function formatDateTime(value) {
    if (!value) return '';
    const d = new Date(value);
    if (isNaN(d)) return '';
    return `${d.getFullYear()}.${pad(d.getMonth() + 1)}.${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/**
 * Харьцангуй хугацаа:
 * < 1 цаг → "X минутын өмнө", < 24 цаг → "X цагийн өмнө",
 * < 7 хоног → "X өдрийн өмнө", дээш нь → "2025.05.01".
 */
export function timeAgo(value) {
    if (!value) return '';
    const then = new Date(value).getTime();
    if (isNaN(then)) return '';
    const diff = (Date.now() - then) / 1000; // секунд

    if (diff < 0) return formatDate(value); // ирээдүйн огноо
    if (diff < 60) return 'дөнгөж сая';
    if (diff < 3600) return Math.floor(diff / 60) + ' минутын өмнө';
    if (diff < 86400) return Math.floor(diff / 3600) + ' цагийн өмнө';
    if (diff < 604800) return Math.floor(diff / 86400) + ' өдрийн өмнө';
    return formatDate(value);
}
