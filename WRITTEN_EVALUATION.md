# Written Evaluation

## 1. Which scenarios did you select to automate, and why those over the alternatives? What did you intentionally leave out, and why?

The assignment required implementing at least eight automated tests across both API endpoints.

Instead of covering every acceptance criterion individually, I selected representative scenarios that provide the broadest functional coverage while demonstrating different testing techniques.

### POST endpoint

Implemented scenarios:

* Successful Media Buyer creation (P1, P2, P3)
* Missing required field — `name` as a representative case (P5)
* Invalid email format (P6)
* Invalid `name` length — parameterized boundary validation (P8)
* Duplicate `mbId` (P11)

These scenarios verify successful behaviour, input validation, uniqueness constraints and boundary conditions.

### GET endpoint

Implemented scenarios:

* Successful response with JSON Schema validation (G1, G2, G4, G5)
* Response contains unique positive IDs (G7)
* `active` values are always `0` or `1` (G6)

These scenarios validate both the API contract and important business rules.

### Intentionally left out

The following criteria were not implemented as individual tests:

**G3** — The `data` field is always an array even when the system has zero media buyers. This scenario requires control over the server state (empty database), which is not possible without test infrastructure. In a real project this would be covered with test data setup/teardown.

**P4** — `active: false` results in `data.active === 0`. This is a distinct scenario from the happy-path test (which only sends `active: true`). It was deprioritised in favour of broader coverage across different validation areas, but it is a meaningful criterion and would be the first addition when extending the suite. The `withActive()` builder method is already in place to support it.

**P5 (partial)** — Missing required fields `mbId`, `email`, and `active` were not tested individually. The `name` scenario was selected as a representative case because the underlying validation mechanism is identical across all required fields — the same `errors` structure with a field-name message. Adding the remaining three would not reveal different API behaviour.

**P7** — `initials` longer than 2 characters returns HTTP 400 with a specific error message. This follows the same length-validation pattern already demonstrated by P8 (`name` boundary). The specific message assertion is also already demonstrated by P6. Both patterns are present in the suite; this criterion adds no new technique.

**P9** — `mbId: "abc"` (non-numeric string) returns HTTP 400. This exercises format validation on `mbId`. It was deprioritised because P6 already demonstrates format-based validation with a more explicit contract-defined error message.

**P10** — `active: "yes"` (non-boolean) returns HTTP 400. This exercises type validation. It was deprioritised as the suite already covers both a type-correct positive path and format validation errors through P6.

Boundary validation for the `name` field was implemented as a parameterized test to avoid duplicated test logic. The same approach would apply to P7 and any future length-constraint scenarios.

---

# 2. Walk through the abstractions you introduced (HTTP client wrapper, factories, schema-validation helper, etc.) and explain what each one buys you when the suite grows from 8 tests to 80.

To keep the project maintainable, I separated responsibilities into dedicated classes.

## MediaBuyerBuilder

The Builder generates reusable request payloads.

Instead of duplicating request JSON inside every test, each test starts from a valid payload and modifies only the fields relevant for that scenario.

I chose Builder over Factory because a Factory would produce complete, fixed objects — meaning each negative test scenario would require either a dedicated factory or combining Factory with Builder anyway. Builder alone makes it straightforward to start from a valid payload and change only the specific field under test, which is more practical for API testing.

Benefits:

* reusable payloads
* no duplicated request bodies
* easier maintenance after API changes
* better readability

---

## MediaBuyerClient

The Client wraps all HTTP communication.

Tests interact only with high-level methods such as:

* `create()`
* `getAll()`

instead of calling `sendPost()` or `sendGet()` directly.

Benefits:

* endpoint URLs exist in one place
* easier maintenance
* reusable API operations
* tests focus on business behaviour instead of HTTP implementation

---

## JSON Schema Validation

Every successful response is validated against the provided JSON Schema.

Schema validation verifies the structure of the response, while test assertions verify business behaviour.

Keeping these responsibilities separate makes the suite much easier to maintain.

---

## Parameterized Tests

Boundary validation was implemented using a parameterized test instead of multiple almost identical test methods.

This reduces duplicated code and makes adding new boundary cases straightforward.

---

# 3. How would you approach contract-drift detection so that a backend change to this API automatically triggers test updates and review? Include tooling and process.

In this project I used JSON Schema validation as a lightweight form of contract testing. In a larger project I would adopt an OpenAPI specification as the single source of truth for the API contract — versioned alongside the application code — so that any change to the contract is explicitly reviewed via a Pull Request and validated in CI.

This approach allows contract changes to be detected before reaching production.

For consumer-driven contract testing I would explore tools such as Pact, though I have not worked with it yet.

---

# 4. If you owned the QA process for this API, which tools (including AI) would you use for test generation, maintenance, flakiness detection, and reporting — and why?

For API automation I would primarily use:

* PHP
* Codeception
* REST module
* JSON Schema validation

For CI/CD:

* GitHub Actions

For reporting:

* So far I have worked with Codeception's native HTML reporter. Going forward I would integrate Allure Report via the allure-codeception adapter, which provides richer HTML reports with test history and timeline — features the native reporter does not offer.

For contract validation:

* OpenAPI specification (as described above)

For AI-assisted development:

* ChatGPT
* Claude Code

I would primarily use AI for:

* generating test ideas
* reviewing test coverage
* generating payload builders
* identifying duplicated assertions
* explaining unfamiliar APIs
* suggesting refactoring opportunities

AI accelerates development, but all generated code should still be reviewed manually.

---

# 5. What is the most challenging situation you have faced in API or end-to-end test automation, and how did you resolve it?

One of the most challenging automation tasks I worked on involved end-to-end testing of a blockchain application using Playwright and MetaMask (synpress). Transactions were asynchronous and depended on wallet confirmations, blockchain processing and multiple backend services, which occasionally caused unstable test execution.

To improve reliability, I focused on reducing unnecessary UI interactions, introducing reusable Page Objects and test utilities, synchronizing tests with the application state instead of relying on fixed waits, and isolating test data wherever possible. I also spent time determining whether failed tests were caused by actual application defects or by external dependencies such as MetaMask, blockchain transaction timing, or unstable test environments.

These changes significantly reduced flaky tests, improved maintainability and made investigating failures much faster.

This experience taught me that long-term stability and maintainability are often more valuable than simply increasing the number of automated tests.
