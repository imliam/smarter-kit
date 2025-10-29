# Testing

- If asked to write a test, never start with implementation. If asked to write a test, just write the test
- Every time a test has been updated, run the test
- When writing a test, don't start fixing other parts of the tests or implementation code - even if you see a linter error you really want to fix. Don't get distracted, and focus on writing the test you have been asked to write
- If you have created new code, generate a test for it
- If you have refactored or restructured code, run the tests afterwards
- Prefer feature tests when testing models
- Don't bother making tests for things that are basic Laravel behaviour (eg. casts on a model)
- All unit/feature tests should follow the same directory structure as the main `app/` directory
