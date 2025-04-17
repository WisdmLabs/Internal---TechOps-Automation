const semver = require('semver');

/**
 * Normalizes version strings to semver format
 * @param {string} version Version string to normalize
 * @returns {string} Normalized version string
 */
function normalizeVersion(version) {
    if (!version) return '0.0.0';

    // Remove any 'v' prefix
    version = version.replace(/^v/, '');

    // Handle versions like '24.6' -> '24.6.0'
    const parts = version.split('.');
    while (parts.length < 3) {
        parts.push('0');
    }

    // Handle versions with extra parts like '1.9.4.2'
    if (parts.length > 3) {
        // Keep the first three parts and append the rest with hyphens
        const extra = parts.slice(3).join('-');
        version = `${parts.slice(0, 3).join('.')}-${extra}`;
    } else {
        version = parts.join('.');
    }

    // If version is invalid after normalization, return '0.0.0'
    return semver.valid(version) ? version : '0.0.0';
}

/**
 * Compares two version strings
 * @param {string} currentVersion Current version string
 * @param {string} latestVersion Latest version string
 * @returns {Object} Comparison result with normalized versions
 */
function compareVersions(currentVersion, latestVersion) {
    const normalizedCurrent = normalizeVersion(currentVersion);
    const normalizedLatest = normalizeVersion(latestVersion);

    return {
        currentVersion: currentVersion,
        normalizedCurrent: normalizedCurrent,
        latestVersion: latestVersion,
        normalizedLatest: normalizedLatest,
        hasUpdate: semver.gt(normalizedLatest, normalizedCurrent)
    };
}

/**
 * Validates API response format
 * @param {Object} response API response to validate
 * @returns {boolean} Whether the response is valid
 */
function validateApiResponse(response) {
    if (!response || typeof response !== 'object') return false;
    if (!Array.isArray(response)) return false;
    
    // Check if each item has required properties
    return response.every(item => {
        return item && 
               typeof item === 'object' && 
               typeof item.slug === 'string' && 
               typeof item.version === 'string';
    });
}

module.exports = {
    normalizeVersion,
    compareVersions,
    validateApiResponse
}; 