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

// Format ISO 8601 date for display, e.g. "Fri, 07 Aug 2026 00:00:00 GMT"
export function formatStatusDate(isoDate) {
    const date = new Date(isoDate);
    return isNaN(date.getTime()) ? isoDate : date.toUTCString();
}
