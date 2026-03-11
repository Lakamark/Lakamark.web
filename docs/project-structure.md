# Project Structure

The application is organized into three main layers to separate concerns and keep the codebase maintainable.

## Domain

The Domain layer contains the core business logic of the application.

Each domain represents a business area such as:

- Auth
- Blog
- Project
- Subscription

The domain layer defines:

- entities
- services
- domain events
- value objects
- enums

It expresses **what the application does** without being tied to web or infrastructure concerns.

---

## Http

The Http layer handles the web interface.

This includes:

- controllers
- requests
- responses
- Twig extensions
- security voters

Its responsibility is to receive requests, delegate work to domain services, and return responses.

---

## Foundation

The Foundation layer provides reusable technical tools used across the application.

These components remain generic and independent from specific business domains.

Examples include:

- mail builders
- security utilities
- token hashing tools
- technical helpers