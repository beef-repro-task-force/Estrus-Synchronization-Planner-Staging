// Bundled JSON as fallback
import ProtocolsJSON from '../components/Protocols.json';
import RulesJSON from '../components/test-rules.json';

// Default metadata for parameters (dosages, headers)
const defaultParametersMeta = {
    'GnRH': {
        'Cystorelin': { dosage: '2cc' },
        'Factrel':    { dosage: '2cc' },
        'Fertagyl':   { dosage: '2cc' },
        'OvaCyst':    { dosage: '2cc' },
        'GONAbreed':  { dosage: '1cc' },
    },
    'PG': {
        'Estrumate':        { dosage: '2cc' },
        'EstroPLAN':        { dosage: '2cc' },
        'InSynch':          { dosage: '5cc' },
        'Lutalyse':         { dosage: '5cc' },
        'ProstaMate':       { dosage: '5cc' },
        'Lutalyse HighCon': { dosage: '2cc' },
        'Synchsure':        { dosage: '2cc' },
    },
    'System Type': {
        'Estrus AI':               { header: 'Heat Detect & Breed' },
        'Estrus AI + Clean-up AI': { header: 'Heat Detect & Clean-up AI' },
        'Fixed-Time AI':           { header: 'Fixed-Time AI' },
        'Split Time AI':           { header: 'Split Time AI' },
    },
};

/**
 * Get protocols data - WordPress injected or fallback to bundled JSON
 */
export function getProtocolsData() {
    if (typeof window !== 'undefined' && window.BEEF_APP_PROTOCOLS) {
        console.log('Using WordPress ACF protocols data');
        return window.BEEF_APP_PROTOCOLS;
    }
    console.log('Falling back to bundled Protocols.json');
    // Add default ParametersMeta if not present in bundled JSON
    if (!ProtocolsJSON.ParametersMeta) {
        ProtocolsJSON.ParametersMeta = defaultParametersMeta;
    }
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
