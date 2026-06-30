# API Automation Test Task

## Overview

This repository contains a sample API automation framework implemented in **PHP** using **Codeception**.

The objective of this assignment was to demonstrate a clean, maintainable and scalable API automation framework based on the provided API contract.

The solution focuses on:

* maintainable test architecture
* reusable request payloads
* clear separation between
    - test data creation
    - API communication
    - test assertions
* API contract validation
* scalability

---

# Codeception Setup

The project is designed around a typical Codeception API testing setup using the following modules:

* REST
* PhpBrowser
* Asserts

In a real project the Base URL would be configured through Codeception configuration and environment variables instead of being hardcoded inside the tests.

Successful responses are validated against the JSON Schemas provided in the assignment.

---

# Repository Structure

```text
src/
├── Builder/
│   └── MediaBuyerBuilder.php
│
└── Client/
    └── MediaBuyerClient.php

tests/
├── Api/
│   ├── CreateMediaBuyerCest.php
│   └── GetMediaBuyersCest.php
│
└── schemas/
    ├── get-media-buyers-schema.json
    └── post-media-buyer-schema.json
```

---

# Project Architecture

## MediaBuyerBuilder

The Builder is responsible for creating reusable request payloads.

Instead of duplicating request JSON across multiple tests, every test starts from a default payload and changes only the fields required for the scenario.

Example:

```php
$payload = MediaBuyerBuilder::create()
    ->withEmail('not-an-email')
    ->build();
```

### Benefits

* reusable payloads
* no duplicated request bodies
* easier maintenance after API changes
* readable test scenarios

---

## MediaBuyerClient

The Client encapsulates all HTTP communication with the API.

Tests never call `sendGet()` or `sendPost()` directly. Instead, all communication goes through the client abstraction.

### Benefits

* endpoint URLs exist in one place
* easier maintenance
* tests focus on business behaviour instead of HTTP implementation
* HTTP implementation can change without modifying the tests

---

## Test Classes

### CreateMediaBuyerCest

Contains automated tests for the **POST /api/mediabuyers** endpoint.

### GetMediaBuyersCest

Contains automated tests for the **GET /api/mediabuyers** endpoint.

---

# Selected Test Scenarios

The assignment requires at least **8 automated tests**.

Instead of implementing every acceptance criterion, I selected representative scenarios that provide broad functional coverage across both endpoints while demonstrating different testing techniques (positive path, validation, schema validation and business rules).

## POST

* Create Media Buyer successfully — P1, P2, P3
* Missing required field (`name` as representative case) — P5
* Invalid email — P6
* Invalid `name` length (parameterized boundary test) — P8
* Duplicate `mbId` — P11

## GET

* Successful response with JSON Schema validation — G1, G2, G4, G5
* Response contains unique positive IDs — G7
* `active` values are always `0` or `1` — G6

## Intentionally left out

| Criterion | Reason |
|-----------|--------|
| G3 | Requires control over server state (empty database) — not possible without test infrastructure |
| P4 | `active: false → 0` is a distinct scenario; deprioritised in favour of broader validation coverage, `withActive()` is ready to add it |
| P5 (mbId, email, active) | Same validation mechanism as the `name` case — identical `errors` structure, no new behaviour revealed |
| P7 | Same length-validation pattern as P8; same specific-message pattern as P6 — both already in the suite |
| P9 | Same format-validation pattern as P6 |
| P10 | Same type-validation category; positive path already covered by the happy-path test |

Full reasoning is in the Written Evaluation document (submitted separately).

---

# Why These Scenarios?

The selected scenarios cover the most important aspects of the API:

* successful endpoint behaviour
* request validation
* response contract validation
* business rules
* uniqueness constraints
* boundary value validation

Boundary validation for the `name` field was implemented as a **parameterized test**, demonstrating how repetitive validation scenarios can be covered without duplicating test logic.

---

# Introduced Abstractions

## Builder

The Builder centralizes request payload creation.

Benefits:

* reusable request payloads
* single place for future payload updates
* improved readability
* less duplicated code

---

## Client

The Client abstracts HTTP communication.

Benefits:

* endpoint URLs exist in one place
* easier maintenance
* reusable API operations
* tests remain focused on behaviour rather than implementation details

---

## JSON Schema Validation

Every successful response is validated against the JSON Schema supplied with the assignment.

Schema validation verifies the response structure, while individual assertions verify business behaviour.

Keeping these responsibilities separate makes the test suite easier to maintain as the API evolves.

---

# Future Improvements

If this framework were expanded into a production-ready solution, I would additionally introduce:

* request and response fixtures
* reusable response assertion helpers
* test data setup and teardown
* environment-specific configuration using `.env`
* parallel execution
* CI/CD integration
* contract testing using OpenAPI
* schema versioning
* Allure reporting
* mock server integration (Prism or WireMock)

---

# Assumptions

The API contract was treated as the source of truth.

Whenever the specification intentionally left behaviour undefined, assumptions were documented instead of being hardcoded into the tests.

### Duplicate `mbId`

The assignment explicitly allows either **HTTP 400** or **HTTP 409** when creating duplicate `mbId` values.

This solution assumes **HTTP 409 Conflict**, as it is the most common RESTful response for uniqueness violations.

### Validation Messages

Validation messages are verified only where they are explicitly defined by the specification.

When the contract specifies only the expected HTTP status code, no assumptions are made about the response body.

---

# Summary

The resulting solution demonstrates a scalable API automation structure based on common testing practices:

* Builder pattern for request payloads
* Client abstraction for API communication
* JSON Schema validation
* Arrange–Act–Assert test structure
* independent test data
* parameterized validation tests
* clean separation of responsibilities

The project is intentionally structured so that additional endpoints and acceptance criteria can be added with minimal changes to the existing code.
