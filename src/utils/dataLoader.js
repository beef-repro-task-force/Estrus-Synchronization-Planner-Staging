// Bundled JSON as fallback
import ProtocolsJSON from '../components/Protocols.json';
import RulesJSON from '../components/test-rules.json';

/**
 * Get protocols data - WordPress injected or fallback to bundled JSON
 */
export function getProtocolsData() {
    if (typeof window !== 'undefined' && window.BEEF_APP_PROTOCOLS) {
        console.log('Using WordPress ACF protocols data');
        return window.BEEF_APP_PROTOCOLS;
    }
    console.log('Falling back to bundled Protocols.json');
    return ProtocolsJSON;
}

/**
 * Get rules data - WordPress injected or fallback to bundled JSON
 */
export function getRulesData() {
    if (typeof window !== 'undefined' && window.BEEF_APP_RULES) {
        console.log('Using WordPress ACF rules data');
        return window.BEEF_APP_RULES;
    }
    console.log('Falling back to bundled test-rules.json');
    return RulesJSON;
}
