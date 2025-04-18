import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import GenerateShoppingListModal from '../GenerateShoppingListModal.vue'

// Create a shared mock instance for useForm
const mockForm = {
  name: '',
  period: 'full',
  errors: {},
  processing: false,
  post: vi.fn().mockImplementation(() => Promise.resolve()),
  reset: vi.fn(),
  clearErrors: vi.fn()
}

// Mock useForm to return the shared instance
vi.mock('@inertiajs/vue3', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@inertiajs/vue3')>()
  return {
    ...actual,
    useForm: vi.fn(() => mockForm)
  }
})

// Mock route function
vi.stubGlobal('route', (name: string, params?: any) => {
  return `/api/route/${name}/${params}`
})

// Mock date utility
vi.mock('@/utils/date', () => ({
  formatStartDate: vi.fn((dateStr) => new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
}))

describe('GenerateShoppingListModal', () => {
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
    DialogFooter: {
      template: '<div class="dialog-footer"><slot></slot></div>'
    },
    Button: {
      template: '<button :class="variant" :type="type" :disabled="disabled"><slot></slot></button>',
      props: ['variant', 'type', 'disabled']
    },
    Label: {
      template: '<label :class="className"><slot></slot></label>',
      props: ['className']
    },
    Input: {
      template: '<input :id="id" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" :type="type" :class="className" :placeholder="placeholder" />',
      props: ['id', 'modelValue', 'type', 'className', 'placeholder'],
      emits: ['update:modelValue']
    },
    InputError: {
      template: '<div v-if="message" class="input-error">{{ message }}</div>',
      props: ['message']
    },
    // No need to stub select as it's a native element
  }

  const defaultProps = {
    open: true,
    mealPlanId: 123,
    mealPlanName: 'Weekly Plan',
    mealPlanStartDate: '2024-07-15',
    mealPlanDuration: 14,
    hasMealsToCook: true
  }

  beforeEach(() => {
    vi.clearAllMocks()
    mockForm.reset()
    mockForm.clearErrors()
    mockForm.errors = {}
    mockForm.name = ''
    mockForm.period = 'full'
  })

  it('initializes form with correct default values when opened', async () => {
    mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    expect(mockForm.name).toBe('')
    expect(mockForm.period).toBe('full')
  })

  it('displays the correct title and description', () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    expect(wrapper.find('.dialog-title').text()).toBe('Generate Shopping List')
    expect(wrapper.find('.dialog-description').text()).toContain('Create a shopping list from this meal plan')
  })

  it('emits update:open event when cancel button is clicked', async () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    const cancelButton = wrapper.findAll('button').find(btn => btn.text() === 'Cancel')
    await cancelButton?.trigger('click')

    expect(wrapper.emitted('update:open')).toBeTruthy()
    expect(wrapper.emitted('update:open')?.[0]).toEqual([false])
  })

  it('shows warning message when hasMealsToCook is false', () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: { ...defaultProps, hasMealsToCook: false },
      global: {
        stubs
      }
    })

    const warningMessage = wrapper.find('.text-amber-800') // Find by class
    expect(warningMessage.exists()).toBe(true)
    expect(warningMessage.text()).toContain('No meals marked "to cook"')
  })

  it('does not show warning message when hasMealsToCook is true', () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    const warningMessage = wrapper.find('.text-amber-800') // Find by class
    expect(warningMessage.exists()).toBe(false)
  })

  it('disables submit button when hasMealsToCook is false', () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: { ...defaultProps, hasMealsToCook: false },
      global: {
        stubs
      }
    })

    const submitButton = wrapper.findAll('button').find(btn => btn.text() === 'Generate List')
    expect(submitButton?.attributes('disabled')).toBeDefined()
  })

  it('enables submit button when hasMealsToCook is true', () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    const submitButton = wrapper.findAll('button').find(btn => btn.text() === 'Generate List')
    expect(submitButton?.attributes('disabled')).toBeUndefined()
  })

  it('updates form period when select changes', async () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    const select = wrapper.find('select')
    await select.setValue('week1')
    expect(mockForm.period).toBe('week1')
  })

  it('submits the form with correct data when Generate List button is clicked', async () => {
    const wrapper = mount(GenerateShoppingListModal, {
      props: defaultProps,
      global: {
        stubs
      }
    })

    // Set a name and period for the test
    mockForm.name = 'Test List'
    mockForm.period = 'week1'

    const form = wrapper.find('form')
    await form.trigger('submit.prevent')

    expect(mockForm.post).toHaveBeenCalledOnce()
    expect(mockForm.post).toHaveBeenCalledWith(
      expect.stringContaining(`meal-plans.generate-shopping-list/${defaultProps.mealPlanId}`),
      expect.objectContaining({
        onSuccess: expect.any(Function)
      })
    )
  })
  
  it('formats period labels correctly', async () => {
    const wrapper = mount(GenerateShoppingListModal, {
        props: defaultProps,
        global: {
            stubs
        }
    })

    const options = wrapper.findAll('option')
    expect(options.length).toBe(3) // Full, Week 1, Week 2 for duration 14
    expect(options[0].text()).toContain('Full Plan (Jul 15 - Jul 28)') // Based on mocked date format
    expect(options[1].text()).toContain('Week 1 (Jul 15 - Jul 21)')
    expect(options[2].text()).toContain('Week 2 (Jul 22 - Jul 28)')
  })
}) 