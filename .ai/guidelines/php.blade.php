# Write great PHP code

- Use the latest PHP features, such as short closures, nullsafe operator, constructor promotion and match expressions
- Never include a docblock where a typehint is sufficient - only use it if the docblock can be more specific (for example `@param array<string, string> $data` or `@return array<string, string>`)
- Favor dependency injection over facades, and avoid using facades unless absolutely necessary
- Implement proper error handling and logging:
  - Use Laravel's exception handling and logging features
  - Create custom exceptions when necessary
  - Use try-catch blocks for expected exceptions
- Always prefer to import classes using the `use` statement rather than using fully qualified class names (FQCN) in the code
- After writing or changing any code, run `composer fix` to automatically fix any code style issues
