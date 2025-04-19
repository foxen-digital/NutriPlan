# Documentation: CollectionController.php

Original file: `app/Http/Controllers/CollectionController.php`

# CollectionController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [index](#index)
  - [show](#show)
  - [store](#store)
  - [update](#update)
  - [destroy](#destroy)
- [Routes Handled](#routes-handled)
- [Model Relationships](#model-relationships)

## Introduction
The `CollectionController` class is responsible for managing user-defined collections within the NutriPlan application. It provides methods to display, create, update, and delete collections, which typically group recipes together. This controller interacts with the `Collection` model and facilitates communication between the view layer (Inertia.js) and the data layer (database).

## Methods

### index

```php
public function index(Request $request): Response|\Illuminate\Http\JsonResponse
```

#### Purpose
The `index` method retrieves a list of collections for the authenticated user.

#### Parameters
- `Request $request`: An instance of the current HTTP request, which provides access to user authentication and request data.

#### Return Values
- `Response`: Renders the collection list view if the request expects HTML.
- `JsonResponse`: Returns a JSON representation of the collections if the request expects JSON.

#### Functionality
- Queries the `Collection` model for collections associated with the authenticated user.
- Utilizes `withCount` to append the count of recipes to each collection.
- Orders the collections by the latest creation date.
- Checks if the request expects JSON; if so, returns the data as JSON, otherwise, renders the Inertia.js component for an index view.

### show

```php
public function show(Collection $collection): Response
```

#### Purpose
The `show` method provides detailed information about a specific collection.

#### Parameters
- `Collection $collection`: A collection instance fetched from the database based on the route parameter.

#### Return Values
- `Response`: Renders the view for displaying the selected collection.

#### Functionality
- Uses authorization checks to ensure the user has the right to view the specified collection.
- Loads related recipes and their respective categories and users, ensuring they are ordered by their creation date.
- Renders the `Show` component with the specified collection's detailed information.

### store

```php
public function store(CreateCollectionRequest $request, CreateCollectionAction $action): RedirectResponse
```

#### Purpose
The `store` method handles the creation of new collections.

#### Parameters
- `CreateCollectionRequest $request`: A request object that validates incoming collection data.
- `CreateCollectionAction $action`: An action object responsible for the logic of creating collections.

#### Return Values
- `RedirectResponse`: Redirects the user back to the collections index with a success message.

#### Functionality
- Validates input using `CreateCollectionRequest`.
- Utilizes the `CreateCollectionAction` to handle the authenticated user and validated request data to create a collection.
- Redirects to the index route upon successful creation, informing the user of the successful operation.

### update

```php
public function update(UpdateCollectionRequest $request, Collection $collection): RedirectResponse
```

#### Purpose
The `update` method manages updates to an existing collection.

#### Parameters
- `UpdateCollectionRequest $request`: A request object ensuring the incoming update data is valid.
- `Collection $collection`: The collection instance to be updated.

#### Return Values
- `RedirectResponse`: Redirects to the collection index with a success message indicating the update was successful.

#### Functionality
- Validates the incoming request data using `UpdateCollectionRequest`.
- Updates the specified collection with the validated data.
- Redirects back to the collections index with a confirmation of the successful update.

### destroy

```php
public function destroy(Collection $collection): RedirectResponse
```

#### Purpose
The `destroy` method is used to delete a specific collection.

#### Parameters
- `Collection $collection`: The collection instance that needs to be deleted.

#### Return Values
- `RedirectResponse`: Redirects to the collections index with a success message confirming the deletion.

#### Functionality
- Performs authorization checks to ensure the user is allowed to delete the collection.
- Deletes the specified collection from the database.
- Redirects the user back to the collections index with a notification of the successful deletion.

## Routes Handled
The `CollectionController` handles the following routes typically defined in the web routes file:

| HTTP Method | Endpoint                     | Action               |
|-------------|-------------------------------|----------------------|
| GET         | /collections                  | index                |
| GET         | /collections/{collection}     | show                 |
| POST        | /collections                  | store                |
| PUT/PATCH   | /collections/{collection}     | update               |
| DELETE      | /collections/{collection}     | destroy              |

## Model Relationships
The `Collection` model is typically defined with relationships to other models, most notably:

- **Recipes**: Each collection can have multiple recipes related to it. This is managed using a `BelongsToMany` relationship.

### Important Attributes
- **user_id**: This attribute links the collection to the user who owns it, allowing for user-specific collections.

This documentation aims to provide comprehensive details about the `CollectionController`, helping developers understand its purpose, functionality, and how it integrates within the broader NutriPlan application.