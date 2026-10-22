// Fetch json from given source
export async function fetchJsonData(sourceJson) {
    try {
        const response = await fetch(sourceJson);
        return await response.json();
    } catch (error) {
        console.error('Error fetching JSON:', error);
        throw error; // Re-throw error to be handled in loadGrid
    }
}

// Format ISO 8601 date for display, e.g. "07 Aug 2026, 00:00 UTC"
export function formatStatusDate(isoDate) {
    const date = new Date(isoDate);
    if (isNaN(date.getTime())) {
        return isoDate;
    }

    return new Intl.DateTimeFormat('en-GB', {
        timeZone: 'UTC',
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZoneName: 'short',
    }).format(date);
}
