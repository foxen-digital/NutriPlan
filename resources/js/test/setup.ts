import { cleanup } from '@testing-library/vue'
import { afterEach } from 'vitest'

// runs a cleanup after each test case
afterEach(() => {
    cleanup()
}) 