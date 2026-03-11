← [Documentation](README.md) | [About Project](about-project.md) | [Design Principles](design-principles.md) | [Project Structure](project-structure.md) | [Authentication Flow](auth-flow.md)
---

# Architecture Overview

This project initially started with the default Symfony structure based
on the traditional **Model–View–Controller (MVC)** pattern.

While this structure works well for many applications, the project
gradually evolved toward a more domain-oriented architecture in order
to better separate business logic from framework-specific code.

---

## Initial Symfony MVC Structure

At the beginning, the application followed the typical Symfony
structure where most logic is organized around controllers,
entities, and services.

Example simplified structure:
````
src/
├ Controller
├ Entity
├ Repository
└ Service
````

In this model:

- **Controllers** handle HTTP requests
- **Entities** represent database models
- **Repositories** manage persistence
- **Services** contain additional application logic

While this structure is simple and efficient, as the project grew it
became harder to clearly isolate the core business logic from the
framework and infrastructure layers.

---

## Moving Toward a Domain-Oriented Architecture

To improve maintainability and separation of concerns, the project
gradually evolved toward a structure organized around the **domain
logic of the application**.

Instead of organizing code primarily by framework components
(controllers, entities, etc.), the architecture focuses on the
different responsibilities of the system.

The project structure evolved into the following layers:
````
src/
├ Domain
├ Http
├ Foundation
└ Infrastructure
````

---

## Domain Layer

The **Domain** layer contains the core business logic of the
application.

This includes:

- entities
- domain services
- domain events
- domain rules

The domain layer should remain as independent as possible from the
framework and external infrastructure.

This separation helps keep the business logic clear, testable, and
maintainable.

---

## HTTP Layer

The **Http** layer is responsible for handling incoming requests and
returning responses.

It includes:

- controllers
- security voters
- request handling
- view rendering (Twig)

Controllers remain intentionally thin and delegate most of the work
to services located in the domain layer.

This keeps the HTTP layer focused on request/response concerns rather
than business logic.

---

## Foundation Layer

The **Foundation** layer contains reusable technical components that
can be used across multiple parts of the application.

Examples include:

- captcha system
- mail builders
- shared utilities
- security helpers

These components are not tied to a specific domain but provide
technical capabilities used by the application.

---

## Why "Foundation"?

The **Foundation** layer contains reusable technical components that are
not tied to a specific domain of the application.

While the **Infrastructure** layer focuses on external integrations
such as databases or external services, the Foundation layer provides
generic tools that can be reused across multiple parts of the system.

These components act as building blocks that support the rest of the
application.

The name **Foundation** was chosen to reflect this idea: a set of
technical tools that form the base upon which the rest of the
application can rely.

---

## Infrastructure Layer

The **Infrastructure** layer contains integrations with external
systems and framework-specific implementations.

Examples include:

- database persistence
- external services
- framework adapters

This layer isolates technical details from the domain logic and allows
the core application to remain independent from implementation details.

---

## Architectural Goal

The goal of this architecture is to clearly separate responsibilities
between the different layers of the application.

```
HTTP Layer
↓
Domain Logic
↓
Infrastructure
```

In this structure:

- The **HTTP layer** handles user interaction and request/response logic.
- The **Domain layer** contains the core business rules and workflows.
- The **Infrastructure layer** manages technical integrations such as
  database access and external services.

By keeping the business logic inside the **Domain layer**, the
application becomes easier to maintain, test, and evolve over time.

This approach also allows the project to remain flexible as new
features and domains are introduced.