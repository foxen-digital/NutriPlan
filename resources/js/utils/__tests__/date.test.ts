import { describe, expect, it } from 'vitest';
import { formatDate, formatEndDate, formatStartDate } from '../date';

describe('formatDate', () => {
    it('returns a formatted date string with weekday, month, and day', () => {
        const input = '2024-01-15';
        const expected = new Date(input).toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
        });
        expect(formatDate(input)).toBe(expected);
    });

    it('returns a string type', () => {
        expect(typeof formatDate('2024-06-15')).toBe('string');
    });

    it('formats a summer date correctly', () => {
        const result = formatDate('2024-07-04');
        expect(result).toContain('Jul');
    });

    it('formats a winter date correctly', () => {
        const result = formatDate('2024-12-25');
        expect(result).toContain('Dec');
    });
});

describe('formatStartDate', () => {
    it('returns a formatted date with month, day, and year', () => {
        const input = '2024-01-15';
        const expected = new Date(input).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
        expect(formatStartDate(input)).toBe(expected);
    });

    it('includes the year in the output', () => {
        expect(formatStartDate('2024-06-01')).toContain('2024');
    });

    it('includes year but formatDate does not', () => {
        // formatStartDate has year option, formatDate does not
        const startResult = formatStartDate('2024-01-15');
        const dateResult = formatDate('2024-01-15');
        expect(startResult).toContain('2024');
        expect(dateResult).not.toContain('2024');
    });
});

describe('formatEndDate', () => {
    it('adds duration - 1 days to the start date', () => {
        const start = '2024-01-15';
        const duration = 7;
        const expectedDate = new Date(start);
        expectedDate.setDate(expectedDate.getDate() + duration - 1);
        const expected = expectedDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
        expect(formatEndDate(start, duration)).toBe(expected);
    });

    it('with duration 1 returns same day as start', () => {
        const start = '2024-03-15';
        const expectedDate = new Date(start);
        const expected = expectedDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
        expect(formatEndDate(start, 1)).toBe(expected);
    });

    it('handles month boundary crossings', () => {
        const start = '2024-01-28';
        const duration = 7;
        const expectedDate = new Date(start);
        expectedDate.setDate(expectedDate.getDate() + duration - 1);
        expect(formatEndDate(start, duration)).toBe(
            expectedDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            }),
        );
    });

    it('handles leap year February correctly', () => {
        // 2024 is a leap year; Feb 27 + 3 days = Mar 1
        const start = '2024-02-27';
        const duration = 4;
        const expectedDate = new Date(start);
        expectedDate.setDate(expectedDate.getDate() + duration - 1);
        expect(formatEndDate(start, duration)).toBe(
            expectedDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            }),
        );
    });

    it('handles a 14-day duration', () => {
        const start = '2024-06-01';
        const duration = 14;
        const expectedDate = new Date(start);
        expectedDate.setDate(expectedDate.getDate() + duration - 1);
        expect(formatEndDate(start, duration)).toBe(
            expectedDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            }),
        );
    });
});
