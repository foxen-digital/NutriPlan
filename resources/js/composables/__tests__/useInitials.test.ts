import { describe, expect, it } from 'vitest';
import { getInitials, useInitials } from '../useInitials';

describe('getInitials', () => {
    it('returns empty string for undefined input', () => {
        expect(getInitials(undefined)).toBe('');
    });

    it('returns empty string for empty string', () => {
        expect(getInitials('')).toBe('');
    });

    it('returns the first letter for a single name', () => {
        expect(getInitials('John')).toBe('J');
    });

    it('returns initials of first and last name', () => {
        expect(getInitials('John Doe')).toBe('JD');
    });

    it('uses first and last name only for multi-word names', () => {
        expect(getInitials('John Middle Doe')).toBe('JD');
    });

    it('returns uppercase initials', () => {
        expect(getInitials('john doe')).toBe('JD');
    });

    it('returns uppercase for a single lowercase name', () => {
        expect(getInitials('alice')).toBe('A');
    });

    it('handles leading and trailing whitespace', () => {
        const result = getInitials('  Jane  Smith  '.trim());
        expect(result).toBe('JS');
    });
});

describe('useInitials', () => {
    it('returns an object with getInitials function', () => {
        const { getInitials: fn } = useInitials();
        expect(typeof fn).toBe('function');
    });

    it('getInitials from composable works correctly', () => {
        const { getInitials: fn } = useInitials();
        expect(fn('Jane Doe')).toBe('JD');
    });
});
