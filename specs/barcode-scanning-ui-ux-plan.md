# Barcode Scanning UI/UX Implementation Plan

This document outlines the planned user interface (UI) and user experience (UX) flow for adding items to a shopping list via barcode scanning within the `ShoppingLists/Show.vue` component.

## 1. Triggering the Scan

*   **Location:** `ShoppingLists/Show.vue` header, next to the existing "Add Item" button.
*   **Element:** Add a new `<Button>` component.
*   **Icon/Label:** Use a `BarcodeIcon` or `CameraIcon` from `lucide-vue-next`. Label could be "Scan Item" or just the icon.

## 2. Scanning Interface

*   **Container:** Use a dedicated `Dialog` component (modal) triggered by the "Scan Item" button.
*   **Camera Permissions:**
    *   Request camera permissions (`navigator.mediaDevices.getUserMedia`) immediately upon modal open.
    *   Display a message while waiting for permission or if permission is denied (e.g., "Camera access needed to scan barcodes.").
*   **Camera Feed:**
    *   Display the live camera feed using a `<video>` element within the modal.
    *   Implement a viewfinder overlay (using CSS or SVG) to guide the user in positioning the barcode.
*   **Status Indicator:**
    *   Display dynamic text within the modal (e.g., "Searching for barcode...", "Point camera at barcode").
*   **Controls:** Include a "Cancel" button to close the scanning modal.

## 3. Scanning Process & Feedback

*   **Detection Library:** Utilize `Quagga2` (or the native Barcode Detection API) to process the `<video>` stream in real-time.
*   **Detection Feedback:**
    *   On successful decode:
        *   Provide brief haptic feedback (vibration) if possible/enabled.
        *   Briefly change the viewfinder style (e.g., flash green border).
        *   Pause the video stream/scanning process.
        *   Update status indicator: "Barcode found! Looking up product..."

## 4. Barcode Lookup & Result Handling

*   **Backend Endpoint:** Create a new API route (e.g., `POST /api/barcode-lookup`) that accepts a barcode number. This endpoint will call the external lookup service (e.g., FreeWebAPI).
*   **Frontend Request:** When a barcode is decoded, the frontend sends it to the `/api/barcode-lookup` endpoint.
*   **Loading State:** While waiting for the backend response, replace the camera feed/viewfinder in the modal with a loading spinner/indicator.
*   **Lookup Success:**
    *   **Backend Response:** Returns product data (name, optional category).
    *   **UI Action:**
        *   Close the scanning modal.
        *   Open the existing "Add Item" modal (`Dialog` in `ShoppingLists/Show.vue`).
        *   Pre-fill the `itemForm` with data from the backend response (name, category). Default quantity to 1 or leave blank.
        *   Automatically focus the "Quantity" input field in the "Add Item" modal.
*   **Lookup Failure (Barcode Not Found in External API):**
    *   **Backend Response:** Indicates the barcode was valid but not found in the external database.
    *   **UI Action:**
        *   Keep the scanning modal open (or transition to a "Not Found" state).
        *   Display message: "Product not found for barcode `[barcode number]`. Add manually?"
        *   Provide options:
            *   "Add Manually" button: Closes scan modal, opens blank "Add Item" modal.
            *   "Scan Again" button: Resumes camera feed/scanning.
            *   "Cancel" button: Closes scan modal.
*   **Lookup Failure (API Error / Backend Issue):**
    *   **Backend Response:** Indicates an error occurred during the lookup process (e.g., external API timeout, invalid key, server error).
    *   **UI Action:**
        *   Keep the scanning modal open.
        *   Display generic error: "Could not look up barcode. Please try again or add manually."
        *   Provide options:
            *   "Retry" button: Re-sends the lookup request for the same barcode.
            *   "Scan Again" button: Resumes camera feed/scanning.
            *   "Add Manually" button: Closes scan modal, opens blank "Add Item" modal.
            *   "Cancel" button: Closes scan modal.

## 5. Adding the Item

*   After a successful scan and lookup, the user interacts with the pre-filled "Add Item" modal.
*   Submitting this modal uses the existing `addItem` function and `shopping-lists.items.store` route, requiring no changes to the item storage logic itself.

## Summary of New/Modified Components

1.  **`ShoppingLists/Show.vue`:**
    *   Add "Scan Item" button.
    *   Add new `Dialog` component for the scanning interface.
    *   Add logic to handle camera permissions, initialize scanner, process scan results, call backend lookup, and manage transitions between scanning modal and add item modal based on lookup outcomes.
2.  **New Backend Controller/Route:**
    *   Handle `POST /api/barcode-lookup`.
    *   Validate input barcode.
    *   Call external barcode lookup service.
    *   Return product data or appropriate error responses.
3.  **(Optional) New Vue Composable:**
    *   Consider extracting camera access and scanner initialization/control logic into a reusable Vue composable (`useBarcodeScanner.ts`). 