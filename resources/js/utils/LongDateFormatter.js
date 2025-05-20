// src/utils/ShortDateFormatter.js
export function longDateFormatter(dateString) {
    const date = new Date(dateString);
    const options = {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        // second: "2-digit",
        hour12: false, // Use 24-hour time format
    };
    return new Intl.DateTimeFormat("fr-FR", options).format(date);
}
