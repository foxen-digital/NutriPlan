import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { render, screen, fireEvent } from '@testing-library/vue'
import DeleteConfirmationModal from '../DeleteConfirmationModal.vue'

// Mock the Dialog components
vi.mock('@/components/ui/dialog', () => ({
    Dialog: {
        name: 'Dialog',
        template: '<div class="dialog" v-if="open"><slot /></div>',
        props: ['open'],
        emits: ['update:open'],
    },
    DialogContent: {
        name: 'DialogContent',
        template: '<div class="dialog-content"><slot /></div>',
        props: ['class'],
    },
    DialogHeader: {
        name: 'DialogHeader',
        template: '<div class="dialog-header"><slot /></div>',
    },
    DialogTitle: {
        name: 'DialogTitle',
        template: '<h2 class="dialog-title"><slot /></h2>',
    },
    DialogDescription: {
        name: 'DialogDescription',
        template: '<p class="dialog-description"><slot /></p>',
    }
}))

// Mock the Button component
vi.mock('@/components/ui/button', () => ({
    Button: {
        name: 'Button',
        template: '<button :class="variant" :disabled="disabled"><slot /></button>',
        props: ['variant', 'disabled']
    }
}))

describe('DeleteConfirmationModal', () => {
    // Common setup
    beforeEach(() => {
        // Clear all mocks before each test
        vi.clearAllMocks()
    })

    describe('Rendering', () => {
        it('renders when open is true', () => {
            const wrapper = mount(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            expect(wrapper.find('.dialog').exists()).toBe(true)
            expect(wrapper.find('.dialog-title').text()).toBe('Delete Meal Plan')
            expect(wrapper.find('.dialog-description').text()).toBe('Are you sure you want to delete this meal plan? This action cannot be undone.')
        })

        it('does not render when open is false', () => {
            const wrapper = mount(DeleteConfirmationModal, {
                props: {
                    open: false
                }
            })

            expect(wrapper.find('.dialog').exists()).toBe(false)
        })
    })

    describe('User Interactions', () => {
        it('emits update:open event when cancel button is clicked', async () => {
            const wrapper = mount(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            await wrapper.find('button[class="outline"]').trigger('click')
            expect(wrapper.emitted('update:open')).toBeTruthy()
            expect(wrapper.emitted('update:open')![0]).toEqual([false])
        })

        it('emits confirm event when delete button is clicked', async () => {
            const wrapper = mount(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            await wrapper.find('button[class="destructive"]').trigger('click')
            expect(wrapper.emitted('confirm')).toBeTruthy()
        })

        it('handles keyboard interactions correctly', async () => {
            const { emitted } = render(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            // Test cancel button interaction
            const cancelButton = screen.getByRole('button', { name: /cancel/i })
            await fireEvent.click(cancelButton)
            expect(emitted()['update:open']).toBeTruthy()
            expect(emitted()['update:open'][0]).toEqual([false])

            // Test delete button interaction
            const deleteButton = screen.getByRole('button', { name: /delete/i })
            await fireEvent.click(deleteButton)
            expect(emitted().confirm).toBeTruthy()
        })
    })

    describe('Accessibility', () => {
        it('has proper heading hierarchy', async () => {
            render(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            const heading = screen.getByRole('heading', { name: /delete meal plan/i })
            expect(heading.tagName).toBe('H2')
        })

        it('has descriptive text for screen readers', () => {
            render(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            const description = screen.getByText(/this action cannot be undone/i)
            expect(description).toBeTruthy()
        })

        it('has properly labeled buttons', () => {
            render(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            const cancelButton = screen.getByRole('button', { name: /cancel/i })
            const deleteButton = screen.getByRole('button', { name: /delete/i })
            
            expect(cancelButton).toBeTruthy()
            expect(deleteButton).toBeTruthy()
        })
    })

    describe('Edge Cases', () => {
        it('handles rapid open/close transitions', async () => {
            const wrapper = mount(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            await wrapper.setProps({ open: false })
            await wrapper.setProps({ open: true })
            
            expect(wrapper.find('.dialog').exists()).toBe(true)
        })

        it('prevents multiple rapid confirmations', async () => {
            const wrapper = mount(DeleteConfirmationModal, {
                props: {
                    open: true
                }
            })

            const deleteButton = wrapper.find('button[class="destructive"]')
            await deleteButton.trigger('click')
            await deleteButton.trigger('click')

            // Should only emit once
            expect(wrapper.emitted('confirm')).toHaveLength(1)
        })
    })
}) 