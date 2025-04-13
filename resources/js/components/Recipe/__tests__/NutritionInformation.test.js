import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import NutritionInformation from '../NutritionInformation.vue';

describe('NutritionInformation.vue', () => {
    it('renders nutrition information when provided', () => {
        const nutrition = {
            calories: '240 cal',
            carbohydrate_content: '37g',
            protein_content: '4g',
            fat_content: '9g',
        };

        const wrapper = mount(NutritionInformation, {
            props: {
                nutrition,
            },
        });

        expect(wrapper.text()).toContain('Calories');
        expect(wrapper.text()).toContain('240 cal');
        expect(wrapper.text()).toContain('Carbohydrates');
        expect(wrapper.text()).toContain('37g');
        expect(wrapper.text()).toContain('Protein');
        expect(wrapper.text()).toContain('4g');
        expect(wrapper.text()).toContain('Fat');
        expect(wrapper.text()).toContain('9g');
    });

    it('displays a message when no nutrition information is available', () => {
        const wrapper = mount(NutritionInformation, {
            props: {
                nutrition: null,
            },
        });

        expect(wrapper.text()).toContain('No nutrition information available');
    });

    it('only renders fields that are present in the nutrition data', () => {
        const nutrition = {
            calories: '240 cal',
            // No other fields provided
        };

        const wrapper = mount(NutritionInformation, {
            props: {
                nutrition,
            },
        });

        expect(wrapper.text()).toContain('Calories');
        expect(wrapper.text()).toContain('240 cal');
        expect(wrapper.text()).not.toContain('Carbohydrates');
        expect(wrapper.text()).not.toContain('Protein');
    });

    it('renders all possible nutrition fields when provided', () => {
        const completeNutrition = {
            calories: '240 cal',
            carbohydrate_content: '37g',
            protein_content: '4g',
            fat_content: '9g',
            fiber_content: '2g',
            sugar_content: '5g',
            cholesterol_content: '0mg',
            sodium_content: '200mg',
            saturated_fat_content: '2g',
            trans_fat_content: '0g',
            unsaturated_fat_content: '7g',
            serving_size: '1 serving',
        };

        const wrapper = mount(NutritionInformation, {
            props: {
                nutrition: completeNutrition,
            },
        });

        // Check that all nutrition fields are rendered
        expect(wrapper.text()).toContain('Calories');
        expect(wrapper.text()).toContain('240 cal');

        expect(wrapper.text()).toContain('Carbohydrates');
        expect(wrapper.text()).toContain('37g');

        expect(wrapper.text()).toContain('Protein');
        expect(wrapper.text()).toContain('4g');

        expect(wrapper.text()).toContain('Fat');
        expect(wrapper.text()).toContain('9g');

        expect(wrapper.text()).toContain('Fiber');
        expect(wrapper.text()).toContain('2g');

        expect(wrapper.text()).toContain('Sugar');
        expect(wrapper.text()).toContain('5g');

        expect(wrapper.text()).toContain('Cholesterol');
        expect(wrapper.text()).toContain('0mg');

        expect(wrapper.text()).toContain('Sodium');
        expect(wrapper.text()).toContain('200mg');

        expect(wrapper.text()).toContain('Saturated Fat');
        expect(wrapper.text()).toContain('2g');

        expect(wrapper.text()).toContain('Trans Fat');
        expect(wrapper.text()).toContain('0g');

        expect(wrapper.text()).toContain('Unsaturated Fat');
        expect(wrapper.text()).toContain('7g');

        expect(wrapper.text()).toContain('Serving Size');
        expect(wrapper.text()).toContain('1 serving');
    });

    it('handles undefined nutrition prop correctly', () => {
        const wrapper = mount(NutritionInformation, {
            props: {},
        });

        expect(wrapper.text()).toContain('No nutrition information available');
    });

    it('handles empty nutrition object correctly', () => {
        const wrapper = mount(NutritionInformation, {
            props: {
                nutrition: {},
            },
        });

        expect(wrapper.text()).not.toContain('No nutrition information available');
        expect(wrapper.find('.grid').exists()).toBe(true);
        expect(wrapper.find('.grid').element.children.length).toBe(0);
    });
});
