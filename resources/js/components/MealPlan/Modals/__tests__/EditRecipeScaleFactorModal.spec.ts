import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import EditRecipeScaleFactorModal from '../EditRecipeScaleFactorModal.vue';

describe('EditRecipeScaleFactorModal', () => {
  const mockRecipe = {
    id: 1,
    title: 'Test Recipe',
    servings: 4,
    pivot: {
      id: 101,
      scale_factor: 2.0,
      servings_available: 8
    }
  };

  it('renders the modal with recipe information', async () => {
    const wrapper = mount(EditRecipeScaleFactorModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 123
      },
      global: {
        stubs: {
          Dialog: {
            template: '<div class="dialog-stub"><slot></slot></div>'
          },
          DialogContent: {
            template: '<div class="dialog-content-stub"><slot></slot><slot name="footer"></slot></div>'
          },
          DialogHeader: {
            template: '<div class="dialog-header-stub"><slot></slot></div>'
          },
          DialogTitle: {
            template: '<div class="dialog-title-stub"><slot></slot></div>'
          },
          DialogDescription: {
            template: '<div class="dialog-description-stub"><slot></slot></div>'
          },
          DialogFooter: {
            template: '<div class="dialog-footer-stub"><slot></slot></div>'
          }
        }
      }
    });

    await nextTick();
    
    // Check if modal displays recipe title
    expect(wrapper.text()).toContain('Test Recipe');
    
    // Check if scale factor input is initialized with the recipe's scale factor
    const input = wrapper.find('input[type="number"]');
    expect(input.element.value).toBe('2');
    
    // Verify servings calculation is displayed
    expect(wrapper.text()).toContain('approximately 8 servings');
  });

  it('updates servings when scale factor changes', async () => {
    const wrapper = mount(EditRecipeScaleFactorModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 123
      },
      global: {
        stubs: {
          Dialog: {
            template: '<div class="dialog-stub"><slot></slot></div>'
          },
          DialogContent: {
            template: '<div class="dialog-content-stub"><slot></slot><slot name="footer"></slot></div>'
          },
          DialogHeader: {
            template: '<div class="dialog-header-stub"><slot></slot></div>'
          },
          DialogTitle: {
            template: '<div class="dialog-title-stub"><slot></slot></div>'
          },
          DialogDescription: {
            template: '<div class="dialog-description-stub"><slot></slot></div>'
          },
          DialogFooter: {
            template: '<div class="dialog-footer-stub"><slot></slot></div>'
          }
        }
      }
    });

    // Change scale factor to 1.5
    const input = wrapper.find('input[type="number"]');
    await input.setValue(1.5);
    
    // Verify servings calculation is updated
    expect(wrapper.text()).toContain('approximately 6 servings');
  });

  it('emits save event with updated scale factor', async () => {
    const wrapper = mount(EditRecipeScaleFactorModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 123
      },
      global: {
        stubs: {
          Dialog: {
            template: '<div class="dialog-stub"><slot></slot></div>'
          },
          DialogContent: {
            template: '<div class="dialog-content-stub"><slot></slot><slot name="footer"></slot></div>'
          },
          DialogHeader: {
            template: '<div class="dialog-header-stub"><slot></slot></div>'
          },
          DialogTitle: {
            template: '<div class="dialog-title-stub"><slot></slot></div>'
          },
          DialogDescription: {
            template: '<div class="dialog-description-stub"><slot></slot></div>'
          },
          DialogFooter: {
            template: '<div class="dialog-footer-stub"><slot></slot></div>'
          }
        }
      }
    });

    // Change scale factor to 3.0
    const input = wrapper.find('input[type="number"]');
    await input.setValue(3.0);
    
    // Click save button
    const saveButton = wrapper.findAll('button')[1]; // Second button should be Save
    await saveButton.trigger('click');
    
    // Verify save event is emitted with correct data
    expect(wrapper.emitted('save')).toBeTruthy();
    expect(wrapper.emitted('save')![0]).toEqual([mockRecipe, 3.0]);
  });

  it('emits close event when cancel button is clicked', async () => {
    const wrapper = mount(EditRecipeScaleFactorModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 123
      },
      global: {
        stubs: {
          Dialog: {
            template: '<div class="dialog-stub"><slot></slot></div>'
          },
          DialogContent: {
            template: '<div class="dialog-content-stub"><slot></slot><slot name="footer"></slot></div>'
          },
          DialogHeader: {
            template: '<div class="dialog-header-stub"><slot></slot></div>'
          },
          DialogTitle: {
            template: '<div class="dialog-title-stub"><slot></slot></div>'
          },
          DialogDescription: {
            template: '<div class="dialog-description-stub"><slot></slot></div>'
          },
          DialogFooter: {
            template: '<div class="dialog-footer-stub"><slot></slot></div>'
          }
        }
      }
    });
    
    // Click cancel button
    const cancelButton = wrapper.findAll('button')[0]; // First button should be Cancel
    await cancelButton.trigger('click');
    
    // Verify update:open event is emitted with false
    expect(wrapper.emitted('update:open')).toBeTruthy();
    expect(wrapper.emitted('update:open')![0]).toEqual([false]);
  });
}); 