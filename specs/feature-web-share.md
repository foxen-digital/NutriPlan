# Web Share Target API Integration for NutriPlan

The Web Share Target API provides a more integrated approach to importing recipes into NutriPlan, especially for mobile users. Instead of using browser extensions or bookmarklets, users can share content directly from their device's native share menu.

## Overview

The Web Share Target API allows web applications to register as targets for the operating system's share feature. When a user finds a recipe they want to import, they can use the standard share functionality of their browser or app and select NutriPlan as the destination.

## Prerequisites

- Your NutriPlan application must be a Progressive Web App (PWA)
- A proper web app manifest file
- A registered service worker
- HTTPS is required for the Web Share Target API to function

## Implementation Steps

### 1. Create or Update Web App Manifest

Add a `share_target` entry to your web app manifest file (`manifest.json`):

```json
{
  "name": "NutriPlan",
  "short_name": "NutriPlan",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#4CAF50",
  "theme_color": "#3c803c",
  "icons": [
    {
      "src": "/icons/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ],
  "share_target": {
    "action": "/api/recipes/import-via-share",
    "method": "GET",
    "params": {
      "title": "title",
      "text": "text",
      "url": "url"
    }
  }
}
```

The `share_target` section defines:
- `action`: The endpoint that will process the shared content
- `method`: How the data will be sent (GET or POST)
- `params`: Mapping of web share data to parameter names

### 2. Create a Backend Handler

Create an endpoint at `/api/recipes/import-via-share` to process the incoming shared content:

```php
// Example PHP handler
Route::get('/api/recipes/import-via-share', function (Request $request) {
    $url = $request->input('url');
    $title = $request->input('title');
    $text = $request->input('text');
    
    // Check authentication and process the recipe import
    // ...
    
    // Redirect to a confirmation page
    return redirect('/recipes/importing')->with('message', 'Recipe import started!');
});
```

### 3. Register a Service Worker

Ensure you have a service worker registered that can intercept and handle share requests:

```javascript
// service-worker.js
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Handle share target requests
  if (url.pathname === '/api/recipes/import-via-share') {
    // You can add special handling here if needed
    console.log('Share target request received');
  }
});
```

### 4. Authentication Approaches

Since the Web Share Target API doesn't inherently handle authentication, you'll need a strategy:

#### Option 1: Session-based Authentication

Rely on the user's existing session cookies if they're already logged into your PWA:

```javascript
// In your share target handler
if (!userIsAuthenticated()) {
  // Store the shared URL in session
  storeSharedContentInSession(sharedUrl);
  // Redirect to login
  return redirect('/login?redirect=pending-share');
}
```

After login, check for and process any pending shared content.

#### Option 2: API Token in Local Storage

Store the user's API token in the PWA's local storage:

```javascript
// In your share target handler
const apiToken = localStorage.getItem('nutriplan-api-token');
const appUrl = localStorage.getItem('nutriplan-app-url');

if (!apiToken || !appUrl) {
  // Store shared content temporarily
  sessionStorage.setItem('pending-shared-url', sharedUrl);
  // Redirect to settings page
  window.location.href = '/settings?pending-share=true';
  return;
}

// Proceed with API call using the stored token
```

#### Option 3: Configuration Page

If no token is found, redirect to a configuration page where the user can enter their API credentials before processing the shared content.

## User Experience

1. User finds a recipe on a website
2. User taps the Share button in their browser
3. NutriPlan appears in the share targets list
4. User selects NutriPlan
5. If authenticated, the recipe is imported
6. If not authenticated, user is prompted to log in

## Limitations

- Currently best supported on Chrome for Android
- Limited support on iOS (requires saving the PWA to home screen)
- Not widely supported on desktop browsers
- Requires users to install your PWA

## Security Considerations

- API tokens should be stored securely (similar security considerations as with extensions or bookmarklets)
- Consider offering the option to clear stored credentials
- Implement token expiration and refresh mechanisms

## Advantages Over Browser Extensions

- No need to install browser extensions
- Works across different browsers (where supported)
- More native mobile experience
- More integrated with the operating system
- Updates automatically with your PWA

## Testing

To test your Web Share Target implementation:
1. Install your PWA on a supported device
2. Visit a recipe website
3. Use the share button
4. Select your PWA from the share menu
5. Verify the recipe URL is properly received and processed 