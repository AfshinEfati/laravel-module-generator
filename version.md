// version 6.2.4 changes
- Form Requests transform `unique:` pipe rules into ignore-aware `Rule::unique()` objects when generating update validators.
- Validation arrays are normalised so Rule instances and string pipes can coexist without being flattened incorrectly.
- Resource responses keep routing boolean/datetime fields through `StatusHelper` for consistent API payloads.
