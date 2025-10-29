# Do things the Laravel way

- Always try to do things the Laravel way as much as possible
- When making new code or modifying code, make sure to also make relevant changes to all related models, casts, policies, factories, migrations, seeders, tests, Filament pages, Filament forms, Filament Infolists, Filament tables, Filament widgets, Filament resources, Filament custom blocks, etc.
- Models are unguarded by default, so NEVER include a `$fillable` or `$guarded` property in models
- Never use enum columns in migrations - use string columns instead
- Always check if a migration already exists before creating a new one, if it's recent it might be fine to modify an existing migration instead
- NEVER run `php artisan serve` or open browser windows to check something
- Make sure all models extend our custom `App\Models\Model` class, which extends `Illuminate\Database\Eloquent\Model` and includes our custom methods and properties
- When thinking about a table, consider if we are referring to the Filament table or the database table
- When creating a new Filament resource, consider what existing resources do; do we need to add it to a navigation group, to global search, etc.?
- Ensure that any model with a slug uses the `HasSlug` trait and defines the `getRouteKeyName` method to return 'slug'
