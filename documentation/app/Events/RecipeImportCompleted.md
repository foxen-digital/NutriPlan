# Documentation: RecipeImportCompleted.php

Original file: `app/Events/RecipeImportCompleted.php`

# RecipeImportCompleted Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Properties](#class-properties)
- [Constructor](#constructor)
- [Methods](#methods)
  - [broadcastOn](#broadcaston)
  - [broadcastAs](#broadcastas)
  - [broadcastWith](#broadcastwith)

## Introduction
The `RecipeImportCompleted` class is part of the `App\Events` namespace in the NutriPlan PHP application. It serves the purpose of broadcasting a notification event upon the completion of a recipe import process. This class implements the `ShouldBroadcast` interface provided by Laravel, enabling the application to send real-time updates to users regarding the status of their recipe imports. 

When executed, this event communicates whether the import was successful or encountered an error, accompanied by a user-friendly message and pertinent details about the recipe imported.

## Class Properties
The `RecipeImportCompleted` class has the following properties:

| Property Name | Type           | Description                                                    |
|---------------|----------------|----------------------------------------------------------------|
| `$userId`     | `int`          | The ID of the user to notify                                   |
| `$status`     | `string`       | The status of the import ('success' or 'error')              |
| `$message`    | `string`       | A user-friendly message regarding the status of the import    |
| `$recipe`     | `Recipe|null`  | The recipe instance that was imported, or null if not applicable  |

### Summary of Properties:
- **userId**: Identifies the user who initiated the import.
- **status**: Indicates the outcome of the import process.
- **message**: Provides a meaningful context for the import result.
- **recipe**: Contains details of the imported recipe when applicable.

## Constructor
```php
public function __construct(
    public readonly int $userId,
    public readonly string $status,
    public readonly string $message,
    public readonly ?Recipe $recipe
) {
}
```

### Purpose
The constructor initializes a new instance of the `RecipeImportCompleted` event, setting its properties based on the parameters passed when the event is dispatched.

### Parameters
- `int $userId`: The ID of the user to inform regarding the import status.
- `string $status`: Indicates whether the import was successful or an error occurred.
- `string $message`: A user-friendly message that communicates the import status.
- `Recipe|null $recipe`: An instance of the `Recipe` model that represents the successfully imported recipe, or `null` if there is no recipe (for error cases).

### Return Values
- The constructor does not return a value but sets the properties of the class instance based on the given inputs.

## Methods

### broadcastOn
```php
public function broadcastOn(): array
{
    return [
        new PrivateChannel('user.' . $this->userId),
    ];
}
```

#### Purpose
This method defines the channels the event should broadcast on. In this case, it returns a private channel specifically for the user identified by `$userId`.

#### Return Values
- Returns an array that contains the `PrivateChannel` instance for the specified user.

### broadcastAs
```php
public function broadcastAs(): string
{
    return 'recipe.import.completed';
}
```

#### Purpose
This method specifies the name under which the event will be broadcast.

#### Return Values
- Returns a string identifier for the event, which is `'recipe.import.completed'`. This name can be used on the client-side to listen for the event.

### broadcastWith
```php
public function broadcastWith(): array
{
    return [
        'status' => $this->status,
        'message' => $this->message,
        'recipeId' => $this->recipe->id,
        'recipeUrl' => route('recipes.show', $this->recipe->slug),
    ];
}
```

#### Purpose
This method provides the data that will be sent along with the broadcast event. It shapes the payload that clients will receive when they listen for this event.

#### Return Values
- Returns an array containing:
  - `status`: The status of the import.
  - `message`: The user-friendly message about the import.
  - `recipeId`: The ID of the recipe that was imported (or `null` if not applicable).
  - `recipeUrl`: The URL to view the imported recipe using the route helper and the recipe's slug.

### Example of Broadcast Payload
Example JSON structure of the broadcast data could look like:
```json
{
    "status": "success",
    "message": "Recipe imported successfully.",
    "recipeId": 123,
    "recipeUrl": "http://example.com/recipes/view-delicious-salad"
}
```

## Conclusion
The `RecipeImportCompleted` class is integral for informing users in real-time about the status of their recipe imports within the NutriPlan application. By leveraging Laravel's broadcasting capabilities, it enhances user engagement and ensures that users are updated as processes are completed. Understanding this event is essential for developers working with user notifications and real-time data handling in Laravel applications.