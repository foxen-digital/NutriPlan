import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import RemoveRecipeModal from '../RemoveRecipeModal.vue'
import type { Recipe } from '@/types/recipe'

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
  router: {
    delete: vi.fn()
  }
}))

// Mock route function
vi.stubGlobal('route', (name: string, params: any) => {
  return `/api/route/${name}/${JSON.stringify(params)}`
})

// Import the router after mocking
import { router } from '@inertiajs/vue3'

describe('RemoveRecipeModal', () => {
  // Create a partial mock recipe with required properties
  const mockRecipe = {
    id: 1,
    title: 'Test Recipe',
    // Other properties would be here in a real implementation
  } as unknown as Recipe

  beforeEach(() => {
    vi.clearAllMocks()
  })

  // Create stubs for UI components
  const stubs = {
    Dialog: {
      template: '<div class="dialog"><slot></slot></div>',
      props: ['open']
    },
    DialogContent: {
      template: '<div class="dialog-content"><slot></slot></div>'
    },
    DialogHeader: {
      template: '<div class="dialog-header"><slot></slot></div>'
    },
    DialogTitle: {
      template: '<h2 class="dialog-title"><slot></slot></h2>'
    },
    DialogDescription: {
      template: '<p class="dialog-description"><slot></slot></p>'
    },
    Button: {
      template: '<button :class="variant"><slot></slot></button>',
      props: ['variant']
    }
  }

  it('emits update:open event when close button is clicked', async () => {
    const wrapper = mount(RemoveRecipeModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 1
      },
      global: {
        stubs
      }
    })
    
    // Find the cancel button and click it
    const cancelButton = wrapper.find('button.outline')
    await cancelButton.trigger('click')
    
    // Check that the update:open event was emitted with the correct value
    expect(wrapper.emitted('update:open')).toBeTruthy()
    expect(wrapper.emitted('update:open')?.[0]).toEqual([false])
  })

  it('calls router.delete with correct parameters when remove button is clicked', async () => {
    const wrapper = mount(RemoveRecipeModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 1
      },
      global: {
        stubs
      }
    })
    
    // Find the remove button and click it
    const removeButton = wrapper.find('button.destructive')
    await removeButton.trigger('click')
    
    // Check that router.delete was called with the correct parameters
    expect(router.delete).toHaveBeenCalledTimes(1)
    expect(router.delete).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        preserveScroll: true,
        onSuccess: expect.any(Function)
      })
    )
  })

  it('does not call router.delete when recipe is null', async () => {
    const wrapper = mount(RemoveRecipeModal, {
      props: {
        open: true,
        recipe: null,
        mealPlanId: 1
      },
      global: {
        stubs
      }
    })
    
    // Find the remove button and click it
    const removeButton = wrapper.find('button.destructive')
    await removeButton.trigger('click')
    
    // Check that router.delete was not called
    expect(router.delete).not.toHaveBeenCalled()
  })

  it('displays the recipe title in the confirmation message', () => {
    const wrapper = mount(RemoveRecipeModal, {
      props: {
        open: true,
        recipe: mockRecipe,
        mealPlanId: 1
      },
      global: {
        stubs
      }
    })
    
    // Check that the description contains the recipe title
    const description = wrapper.find('.dialog-description')
    expect(description.text()).toContain('Test Recipe')
  })
}) 