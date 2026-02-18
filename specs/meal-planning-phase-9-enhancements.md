# Meal Planning - Phase 9: Shopping List Unit Conversion

## Overview
This phase introduces backend enhancements for the shopping list, focusing on unit conversion.

## Depends On
- Phase 7b: Automatic Meal Plan Synchronization

## Leads To
- Potential future phases (e.g., external app integration, pantry tracking).

## Core Functionality

### Unit Conversion
- Implement a system for converting between common cooking units (volume: ml, l, tsp, tbsp, cup, pt, qt, gal; weight: g, kg, oz, lb; potentially pieces/counts where applicable).
- Apply unit conversion during ingredient consolidation (Phase 7a generation and Phase 7b updates) to combine items even if their original recipe units differ (e.g., 1 cup flour + 100g flour might be consolidated to a single entry in grams or cups based on a preferred unit or density estimate).
- Allow users to potentially select preferred units for certain ingredients or globally (Advanced Feature).

## Implementation Details

### Database Schema

*(No database schema changes specific to unit conversion itself, but assumes existing structure for ingredients/items)*

### Models

*(No specific model changes required for basic unit conversion logic, assumes existing `ShoppingListItem` and potentially `Ingredient` models)*

### Services

#### UnitConversionService (`App\\Services\\UnitConversionService`)
*(New Service)*
- Methods like `convert(float $quantity, string $fromUnit, string $toUnit): ?float`.
- Maintain conversion factors/rules (potentially in config or DB table).
- Handle incompatible conversions (e.g., volume to weight without density).

### Controllers

*(No new controllers specific to this phase, assuming UI phase handles display)*

### Form Requests

*(No new form requests specific to this phase)*

### User Interface

#### Views/Components
- *(UI handled in separate phase - See specs/meal-planning-ui-enhancements.md)*
- Unit Conversion display options (e.g., show original unit vs converted unit?).

## Testing Strategy

### Unit Tests
- Test `UnitConversionService` thoroughly for various units and edge cases.

### Feature Tests
- Test ingredient consolidation now correctly uses unit conversion.
- Test automatic updates (Phase 7b) now correctly use unit conversion.

## Future Considerations
- More sophisticated unit conversion (e.g., density tables for volume<->weight).
- User preferences for default units.
- Integration with external shopping apps. 
